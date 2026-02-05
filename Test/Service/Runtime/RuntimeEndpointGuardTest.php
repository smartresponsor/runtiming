<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Test\Service\Runtime;

use App\Runtime\RuntimeSuperchargerContract;
use App\Service\Runtime\RuntimeEndpointGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class RuntimeEndpointGuardTest extends TestCase
{
    /** @var list<string> */
    private array $trustedProxyBackup = [];
    private int $trustedHeaderBackup = 0;

    protected function setUp(): void
    {
        parent::setUp();

        if (method_exists(Request::class, 'getTrustedProxies')) {
            /** @var mixed $proxies */
            $proxies = Request::getTrustedProxies();
            $this->trustedProxyBackup = is_array($proxies) ? array_values(array_filter($proxies, 'is_string')) : [];
        }

        if (method_exists(Request::class, 'getTrustedHeaderSet')) {
            /** @var mixed $header */
            $header = Request::getTrustedHeaderSet();
            $this->trustedHeaderBackup = is_int($header) ? $header : 0;
        }

        Request::setTrustedProxies([], $this->trustedHeaderMask());
    }

    protected function tearDown(): void
    {
        Request::setTrustedProxies($this->trustedProxyBackup, $this->trustedHeaderBackup);
        parent::tearDown();
    }

    public function testSkipForNonRuntimePath(): void
    {
        $result = $this->createGuard()->check($this->createRequest('/healthz'));

        self::assertTrue($result->allowed);
        self::assertSame('skip', $result->reason);
    }

    #[DataProvider('disabledRawProvider')]
    public function testDisabledModeAllowsRequest(string $enabled): void
    {
        $guard = $this->createGuard(['enabled' => $enabled]);

        $result = $guard->check($this->createRequest(RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH));

        self::assertTrue($result->allowed);
        self::assertSame('disabled', $result->reason);
    }

    #[DataProvider('fallbackModeProvider')]
    public function testFallbackModeBehavesAsAllowlistOrToken(string $mode): void
    {
        $guard = $this->createGuard([
            'mode' => $mode,
            'allowCidr' => '198.51.100.0/24',
            'token' => 'unit-token',
        ]);

        $result = $guard->check($this->createRequest(RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH));

        self::assertFalse($result->allowed);
        self::assertSame('tokenMissing', $result->reason);
    }

    public function testAllowlistOnlyAllowsMatchedIpv4AndDeniesMiss(): void
    {
        $guard = $this->createGuard([
            'mode' => 'allowlist_only',
            'allowCidr' => '203.0.113.0/24',
            'token' => '',
        ]);

        $allowed = $guard->check($this->createRequest(RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH, '203.0.113.20'));
        self::assertTrue($allowed->allowed);
        self::assertSame('ip', $allowed->reason);

        $denied = $guard->check($this->createRequest(RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH, '198.51.100.20'));
        self::assertFalse($denied->allowed);
        self::assertSame('ipDenied', $denied->reason);
    }

    public function testAllowlistOnlyAllowsMatchedIpv6(): void
    {
        $guard = $this->createGuard([
            'mode' => 'allowlist_only',
            'allowCidr' => '2001:db8:abcd::/48',
            'token' => '',
        ]);

        $result = $guard->check($this->createRequest(RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH, '2001:db8:abcd::1'));

        self::assertTrue($result->allowed);
        self::assertSame('ip', $result->reason);
    }

    public function testRequireTokenModeHandlesMissingConfig(): void
    {
        $guard = $this->createGuard([
            'mode' => 'require_token',
            'token' => '',
        ]);

        $result = $guard->check($this->createRequest(RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH));

        self::assertFalse($result->allowed);
        self::assertSame('tokenMissingConfig', $result->reason);
    }

    #[DataProvider('tokenInputProvider')]
    public function testTokenParsingContract(array $headers, string $mode, bool $expectedAllowed, string $expectedReason): void
    {
        $guard = $this->createGuard([
            'mode' => $mode,
            'token' => 'unit-token',
            'header' => 'X-Custom-Token',
            'allowCidr' => '203.0.113.0/24',
        ]);

        $result = $guard->check($this->createRequest(RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH, '198.51.100.2', $headers));

        self::assertSame($expectedAllowed, $result->allowed);
        self::assertSame($expectedReason, $result->reason);
    }

    #[DataProvider('edgeBoolishProvider')]
    public function testBoolishEnabledAndProxyStrictHandling(string $enabled, string $proxyStrict, bool $expectedAllowed, string $expectedReason): void
    {
        $guard = $this->createGuard([
            'enabled' => $enabled,
            'proxyStrict' => $proxyStrict,
            'mode' => 'allowlist_only',
            'allowCidr' => '198.51.100.0/24',
            'token' => '',
        ]);

        $request = $this->createRequest(
            RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH,
            '198.51.100.10',
            ['X-Forwarded-For' => '10.0.0.8']
        );

        $result = $guard->check($request);

        self::assertSame($expectedAllowed, $result->allowed);
        self::assertSame($expectedReason, $result->reason);
    }

    public function testProxyStrictDeniesSpoofedForwardHeaderFromUntrustedRemote(): void
    {
        $guard = $this->createGuard([
            'mode' => 'require_token',
            'token' => 'unit-token',
            'proxyStrict' => 'true',
        ]);

        $request = $this->createRequest(
            RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH,
            '198.51.100.10',
            [
                'Authorization' => 'Bearer unit-token',
                'X-Forwarded-For' => '203.0.113.15',
            ]
        );

        $result = $guard->check($request);

        self::assertFalse($result->allowed);
        self::assertSame('proxyHeaderNotTrusted', $result->reason);
    }

    public function testProxyStrictAllowsTrustedProxyByCidr(): void
    {
        Request::setTrustedProxies(['198.51.100.0/24'], $this->trustedHeaderMask());

        $guard = $this->createGuard([
            'mode' => 'require_token',
            'token' => 'unit-token',
            'proxyStrict' => 'true',
        ]);

        $request = $this->createRequest(
            RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH,
            '198.51.100.10',
            [
                'Authorization' => 'Bearer unit-token',
                'X-Forwarded-For' => '203.0.113.15',
            ]
        );

        $result = $guard->check($request);

        self::assertTrue($result->allowed);
        self::assertSame('token', $result->reason);
    }

    public function testInvalidCidrAndInvalidIpDenyDeterministically(): void
    {
        $guard = $this->createGuard([
            'mode' => 'allowlist_only',
            'allowCidr' => 'not-a-cidr,2001:db8::/129',
            'token' => '',
        ]);

        $result = $guard->check($this->createRequest(RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH, '999.1.1.1'));

        self::assertFalse($result->allowed);
        self::assertSame('ipDenied', $result->reason);
    }

    public static function disabledRawProvider(): iterable
    {
        yield 'zero' => ['0'];
        yield 'false' => ['false'];
        yield 'no' => ['no'];
        yield 'off' => ['off'];
    }

    public static function fallbackModeProvider(): iterable
    {
        yield 'strict' => ['strict'];
        yield 'soft' => ['soft'];
        yield 'allowlist' => ['allowlist'];
        yield 'denylist' => ['denylist'];
        yield 'empty string' => [''];
    }

    public static function tokenInputProvider(): iterable
    {
        yield 'missing token in default mode' => [[], 'allowlist_or_token', false, 'tokenMissing'];
        yield 'empty bearer token in default mode' => [['Authorization' => 'Bearer   '], 'allowlist_or_token', false, 'tokenMissing'];
        yield 'malformed auth header in default mode' => [['Authorization' => 'Basic abc'], 'allowlist_or_token', false, 'tokenMissing'];
        yield 'bearer token accepted in require mode' => [['Authorization' => 'Bearer unit-token'], 'require_token', true, 'token'];
        yield 'wrong bearer token rejected in require mode' => [['Authorization' => 'Bearer nope'], 'require_token', false, 'tokenDenied'];
        yield 'custom header token accepted in require mode' => [['X-Custom-Token' => 'unit-token'], 'require_token', true, 'token'];
    }

    public static function edgeBoolishProvider(): iterable
    {
        yield 'empty enabled defaults true and strict true' => ['', '', false, 'proxyHeaderNotTrusted'];
        yield 'string true enabled and strict false' => ['TRUE', 'false', true, 'ip'];
        yield 'string false disables guard' => ['false', 'true', true, 'disabled'];
        yield 'string zero disables guard' => ['0', 'TRUE', true, 'disabled'];
    }

    /**
     * @param array<string, string> $override
     */
    private function createGuard(array $override = []): RuntimeEndpointGuard
    {
        return new RuntimeEndpointGuard(
            $override['enabled'] ?? 'true',
            $override['mode'] ?? 'allowlist_or_token',
            $override['allowCidr'] ?? '10.0.0.0/8,2001:db8::/32',
            $override['token'] ?? 'unit-token',
            $override['header'] ?? 'X-Runtime-Token',
            $override['proxyStrict'] ?? 'true'
        );
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(string $path, string $remoteAddr = '198.51.100.44', array $headers = []): Request
    {
        $server = [
            'REMOTE_ADDR' => $remoteAddr,
            'REQUEST_URI' => $path,
        ];

        foreach ($headers as $name => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return Request::create($path, 'GET', [], [], [], $server);
    }

    private function trustedHeaderMask(): int
    {
        $all = Request::class . '::HEADER_X_FORWARDED_ALL';
        if (defined($all)) {
            /** @var int $value */
            $value = constant($all);
            return $value;
        }

        $set = 0;
        foreach (['HEADER_X_FORWARDED_FOR', 'HEADER_X_FORWARDED_HOST', 'HEADER_X_FORWARDED_PROTO', 'HEADER_X_FORWARDED_PORT', 'HEADER_X_FORWARDED_PREFIX'] as $constant) {
            $fqcn = Request::class . '::' . $constant;
            if (defined($fqcn)) {
                /** @var int $value */
                $value = constant($fqcn);
                $set |= $value;
            }
        }

        return $set;
    }
}
