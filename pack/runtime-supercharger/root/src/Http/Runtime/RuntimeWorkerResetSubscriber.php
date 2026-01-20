<?php
declare(strict_types=1);



namespace App\Http\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerStateInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RuntimeWorkerResetSubscriber implements EventSubscriberInterface
{
    private string $enabledRaw;
    private RuntimeResetRegistryInterface $registry;

    /**
     * @var RuntimeWorkerStateInterface|null
     */
    private ?RuntimeWorkerStateInterface $state;

    public function __construct(
        string $enabled,
        RuntimeResetRegistryInterface $registry,
        ?RuntimeWorkerStateInterface $state
    ) {
        $this->enabledRaw = $enabled;
        $this->registry = $registry;
        $this->state = $state;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['onTerminate', -200],
        ];
    }

    public function onTerminate(TerminateEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->isEnabled()) {
            return;
        }

        // If lifecycle already marked recycle, process will exit; skip extra work.
        if ($this->state instanceof RuntimeWorkerStateInterface && $this->state->isRecyclePending()) {
            return;
        }

        $this->registry->resetAll();
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
}
