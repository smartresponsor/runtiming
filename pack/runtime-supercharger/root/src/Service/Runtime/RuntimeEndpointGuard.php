<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\Runtime\RuntimeSuperchargerContract;
use App\ServiceInterface\Runtime\RuntimeEndpointGuardInterface;
use App\ServiceInterface\Runtime\RuntimeEndpointGuardResult;
use Symfony\Component\HttpFoundation\Request;

final class RuntimeEndpointGuard implements RuntimeEndpointGuardInterface
{
    private string $enabledRaw;
    private string $mode;
    private string $allowCidrRaw;
    private string $token;
    private string $header;
    private string $proxyStrictRaw;

    public function __construct(
        string $enabled,
        string $mode,
        string $allowCidr,
        string $token,
        string $header,
        string $proxyStrict
    ) {
        $this->enabledRaw = $enabled;
        $this->mode = $mode !== '' ? $mode : 'allowlist_or_token';
        $this->allowCidrRaw = $allowCidr;
        $this->token = $token;
        $this->header = $header !== '' ? $header : 'X-Runtime-Token';
        $this->proxyStrictRaw = $proxyStrict;
    }

    public function check(Request $request): RuntimeEndpointGuardResult
    {
        $path = (string) $request->getPathInfo();

        if (!$this->isRuntimeEndpointPath($path)) {
            return RuntimeEndpointGuardResult::allow('skip');
        }

        if (!$this->isEnabled()) {
            return RuntimeEndpointGuardResult::allow('disabled');
        }

        if ($this->isProxyStrictEnabled() && $this->hasProxyHeader($request)) {
            $remote = (string) $request->server->get('REMOTE_ADDR', '');
            if ($remote === '' || !$this->isTrustedProxyRemoteAddr($remote)) {
                return RuntimeEndpointGuardResult::deny('proxyHeaderNotTrusted');
            }
        }

        $ip = $request->getClientIp();
        $ipAllowed = is_string($ip) && $ip !== '' && $this->ipAllowed($ip);

        $token = $this->extractToken($request);
        $tokenOk = $this->token !== '' && $token !== '' && hash_equals($this->token, $token);

        $mode = $this->normalizeMode($this->mode);

        if ($mode === 'allowlist_only') {
            return $ipAllowed ? RuntimeEndpointGuardResult::allow('ip') : RuntimeEndpointGuardResult::deny('ipDenied');
        }

        if ($mode === 'require_token') {
            if ($this->token === '') {
                return RuntimeEndpointGuardResult::deny('tokenMissingConfig');
            }
            return $tokenOk ? RuntimeEndpointGuardResult::allow('token') : RuntimeEndpointGuardResult::deny('tokenDenied');
        }

        // allowlist_or_token
        if ($ipAllowed) {
            return RuntimeEndpointGuardResult::allow('ip');
        }

        if ($this->token !== '' && $tokenOk) {
            return RuntimeEndpointGuardResult::allow('token');
        }

        if ($this->token !== '' && $token === '') {
            return RuntimeEndpointGuardResult::deny('tokenMissing');
        }

        return RuntimeEndpointGuardResult::deny('forbidden');
    }

    private function isEnabled(): bool
    {
        $v = strtolower(trim($this->enabledRaw));
        if ($v === '' || $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on') {
            return true;
        }
        if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
            return false;
        }
        return true;
    }

    private function isProxyStrictEnabled(): bool
    {
        $v = strtolower(trim($this->proxyStrictRaw));
        if ($v === '' || $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on') {
            return true;
        }
        if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
            return false;
        }
        return true;
    }

    private function normalizeMode(string $mode): string
    {
        $m = strtolower(trim($mode));
        if ($m === 'allowlist_only' || $m === 'require_token' || $m === 'allowlist_or_token') {
            return $m;
        }
        return 'allowlist_or_token';
    }

    private function isRuntimeEndpointPath(string $path): bool
    {
        return $path === RuntimeSuperchargerContract::ENDPOINT_METRICS_PATH
            || $path === RuntimeSuperchargerContract::ENDPOINT_METRICS_AGGREGATE_PATH
            || $path === RuntimeSuperchargerContract::ENDPOINT_STATUS_PATH
            || $path === RuntimeSuperchargerContract::ENDPOINT_STATUS_HOST_PATH;
    }

    private function hasProxyHeader(Request $request): bool
    {
        $h = $request->headers;

        return $h->has('Forwarded')
            || $h->has('X-Forwarded-For')
            || $h->has('X-Forwarded-Host')
            || $h->has('X-Forwarded-Proto')
            || $h->has('X-Forwarded-Port')
            || $h->has('X-Forwarded-Prefix');
    }

    private function isTrustedProxyRemoteAddr(string $remoteAddr): bool
    {
        if (!method_exists(Request::class, 'getTrustedProxies')) {
            return false;
        }

        /** @var mixed $list */
        $list = Request::getTrustedProxies();
        if (!is_array($list) || $list === []) {
            return false;
        }

        foreach ($list as $proxy) {
            if (!is_string($proxy)) {
                continue;
            }
            $proxy = trim($proxy);
            if ($proxy === '') {
                continue;
            }

            // Common shortcuts (defensive; FrameworkBundle usually expands these already)
            if ($proxy === 'private_ranges' || $proxy === 'PRIVATE_SUBNETS' || $proxy === 'PRIVATE_RANGES') {
                if ($this->isPrivateSubnet($remoteAddr)) {
                    return true;
                }
                continue;
            }

            if ($proxy === 'REMOTE_ADDR') {
                return true;
            }

            if ($proxy === $remoteAddr) {
                return true;
            }

            if ($this->ipMatchCidr($remoteAddr, $proxy)) {
                return true;
            }
        }

        return false;
    }

    private function isPrivateSubnet(string $ip): bool
    {
        // RFC1918 + loopback + RFC4193 (IPv6 unique local)
        $ranges = [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '127.0.0.0/8',
            '::1/128',
            'fc00::/7',
        ];

        foreach ($ranges as $cidr) {
            if ($this->ipMatchCidr($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    private function extractToken(Request $request): string
    {
        $auth = (string) $request->headers->get('Authorization', '');
        if (stripos($auth, 'Bearer ') === 0) {
            return trim(substr($auth, 7));
        }

        $v = (string) $request->headers->get($this->header, '');
        return trim($v);
    }

    private function ipAllowed(string $ip): bool
    {
        $list = $this->parseAllowCidrList($this->allowCidrRaw);
        foreach ($list as $cidr) {
            if ($this->ipMatchCidr($ip, $cidr)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return list<string>
     */
    private function parseAllowCidrList(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $out = [];
        foreach (explode(',', $raw) as $p) {
            $p = trim($p);
            if ($p !== '') {
                $out[] = $p;
            }
        }
        return $out;
    }

    private function ipMatchCidr(string $ip, string $cidr): bool
    {
        $cidr = trim($cidr);
        if ($cidr === '') {
            return false;
        }

        $parts = explode('/', $cidr, 2);
        $net = trim($parts[0]);
        $prefix = isset($parts[1]) ? (int) trim($parts[1]) : -1;

        $ipBin = @inet_pton($ip);
        $netBin = @inet_pton($net);

        if (!is_string($ipBin) || !is_string($netBin)) {
            return false;
        }

        if (strlen($ipBin) != strlen($netBin)) {
            return false;
        }

        $bits = strlen($ipBin) * 8;
        if ($prefix < 0) {
            $prefix = $bits;
        }
        if ($prefix < 0 || $prefix > $bits) {
            return false;
        }

        $bytes = intdiv($prefix, 8);
        $rem = $prefix % 8;

        for ($i = 0; $i < $bytes; $i++) {
            if (ord($ipBin[$i]) !== ord($netBin[$i])) {
                return false;
            }
        }

        if ($rem === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $rem)) & 0xFF;
        return (ord($ipBin[$bytes]) & $mask) === (ord($netBin[$bytes]) & $mask);
    }
}
