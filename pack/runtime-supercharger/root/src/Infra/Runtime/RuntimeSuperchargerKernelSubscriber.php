<?php
declare(strict_types=1);



namespace App\Infra\Runtime;

use App\Service\Runtime\RuntimeSuperchargerLifecycle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RuntimeSuperchargerKernelSubscriber implements EventSubscriberInterface
{
    private const ATTR_T0 = '_runtime_supercharger_t0';
    private const ATTR_DECISION = '_runtime_supercharger_decision';

    private RuntimeSuperchargerLifecycle $lifecycle;

    public function __construct(RuntimeSuperchargerLifecycle $lifecycle)
    {
        $this->lifecycle = $lifecycle;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 2048],
            KernelEvents::RESPONSE => ['onResponse', -2048],
            KernelEvents::TERMINATE => ['onTerminate', -2048],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $request->attributes->set(self::ATTR_T0, microtime(true));

        $this->lifecycle->onRequestStart();
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $t0 = (float) $request->attributes->get(self::ATTR_T0, microtime(true));
        $dt = microtime(true) - $t0;

        $response = $event->getResponse();
        $status = (int) $response->getStatusCode();

        $decision = $this->lifecycle->onResponse($status, (float) $dt);
        $request->attributes->set(self::ATTR_DECISION, $decision);

        if ($this->lifecycle->shouldRecycle($decision)) {
            $response->headers->set('X-Runtime-Supercharger-Recycle', '1');
            $response->headers->set('X-Runtime-Supercharger-Reason', $this->lifecycle->recycleReason($decision));
        }
    }

    public function onTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $t0 = (float) $request->attributes->get(self::ATTR_T0, microtime(true));
        $dt = microtime(true) - $t0;

        $response = $event->getResponse();
        $status = (int) $response->getStatusCode();

        $decision = $request->attributes->get(self::ATTR_DECISION);
        if (!is_object($decision)) {
            $decision = $this->lifecycle->onResponse($status, (float) $dt);
        }

        $this->lifecycle->onTerminate($status, (float) $dt, $decision);
    }
}
