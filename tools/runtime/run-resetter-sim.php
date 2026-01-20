<?php
declare(strict_types=1);



require_once dirname(__DIR__, 2) . '/vendor/autoload.php';


use App\Service\Runtime\RuntimeResetterChain;
use App\Service\Runtime\RuntimeContainerServiceResetter;
use App\ServiceInterface\Runtime\RuntimeResetReport;

final class FakeServiceWithReset
{
    public int $n = 0;
    public function reset(): void { $this->n++; }
}

final class FakeCache
{
    public int $clearCount = 0;
    public function clear(): bool { $this->clearCount++; return true; }
}

final class FakeContainer
{
    /** @var array<string,object> */
    private array $svc;

    /** @param array<string,object> $svc */
    public function __construct(array $svc)
    {
        $this->svc = $svc;
    }

    public function has(string $id): bool { return array_key_exists($id, $this->svc); }
    public function get(string $id): object { return $this->svc[$id]; }
}

final class FakeKernel
{
    private FakeContainer $container;
    public function __construct(FakeContainer $container) { $this->container = $container; }
    public function getContainer(): FakeContainer { return $this->container; }
}

$svcA = new FakeServiceWithReset();
$svcB = new FakeCache();

$kernel = new FakeKernel(new FakeContainer([
    'svc.reset' => $svcA,
    'cache.app' => $svcB,
]));

$last = null;

$chain = new RuntimeResetterChain([
    new RuntimeContainerServiceResetter(['svc.reset', 'cache.app', 'missing.id'], function (RuntimeResetReport $r) use (&$last): void {
        $last = $r->toArray();
    }),
]);

$chain->reset($kernel);

echo json_encode([
    'svcResetCount' => $svcA->n,
    'cacheClearCount' => $svcB->clearCount,
    'chainReport' => $chain->getLastReportAsArray(),
    'containerReport' => $last,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
