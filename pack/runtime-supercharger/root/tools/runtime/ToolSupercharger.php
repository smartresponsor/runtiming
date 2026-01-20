<?php
declare(strict_types=1);



namespace App\Tool\Runtime;

use App\Tool\Runtime\Fixture\FixtureResetRegistryInterface;

final class ToolSupercharger implements ToolSuperchargerInterface
{
    private FixtureResetRegistryInterface $registry;
    private ?object $servicesResetter;
    private bool $gcEnable;

    public function __construct(FixtureResetRegistryInterface $registry, ?object $servicesResetter = null, bool $gcEnable = false)
    {
        $this->registry = $registry;
        $this->servicesResetter = $servicesResetter;
        $this->gcEnable = $gcEnable;
    }

    public function beforeRequest(): void
    {
    }

    public function afterResponse(int $statusCode): void
    {
        if ($this->servicesResetter !== null && method_exists($this->servicesResetter, 'reset')) {
            $this->servicesResetter->reset();
        }

        $this->registry->resetAll();

        if ($this->gcEnable && function_exists('gc_collect_cycles')) {
            @gc_collect_cycles();
        }
    }
}
