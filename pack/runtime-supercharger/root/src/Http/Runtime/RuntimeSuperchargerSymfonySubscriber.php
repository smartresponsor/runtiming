<?php
declare(strict_types=1);



namespace App\Http\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RuntimeSuperchargerSymfonySubscriber implements EventSubscriberInterface
{
    private RuntimeSuperchargerInterface $supercharger;
    private bool $enableBeforeRequest;
    private bool $enableAfterResponse;

    public function __construct(RuntimeSuperchargerInterface $supercharger, bool $enableBeforeRequest = false, bool $enableAfterResponse = true)
    {
        $this->supercharger = $supercharger;
        $this->enableBeforeRequest = $enableBeforeRequest;
        $this->enableAfterResponse = $enableAfterResponse;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', -128],
            KernelEvents::TERMINATE => ['onTerminate', 128],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$this->enableBeforeRequest) {
            return;
        }
        if (!$event->isMainRequest()) {
            return;
        }

        $this->supercharger->beforeRequest();
    }

    public function onTerminate(TerminateEvent $event): void
    {
        if (!$this->enableAfterResponse) {
            return;
        }
        if (!$event->isMainRequest()) {
            return;
        }

        $status = $event->getResponse()->getStatusCode();
        $this->supercharger->afterResponse($status);
    }
}
