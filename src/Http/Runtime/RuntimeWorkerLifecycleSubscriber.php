<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Http\Runtime;

use App\ServiceInterface\Runtime\RuntimeWorkerLifecyclePolicyInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerStateInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerTerminatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RuntimeWorkerLifecycleSubscriber implements EventSubscriberInterface
{
    private RuntimeWorkerStateInterface $state;
    private RuntimeWorkerLifecyclePolicyInterface $policy;
    private RuntimeWorkerTerminatorInterface $terminator;

    public function __construct(
        RuntimeWorkerStateInterface $state,
        RuntimeWorkerLifecyclePolicyInterface $policy,
        RuntimeWorkerTerminatorInterface $terminator
    ) {
        $this->state = $state;
        $this->policy = $policy;
        $this->terminator = $terminator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 250],
            KernelEvents::RESPONSE => ['onResponse', -50],
            KernelEvents::TERMINATE => ['onTerminate', -255],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->state->onRequestStart();

        $decision = $this->policy->decide($this->state);
        if ($decision->recycle) {
            $this->state->markRecycle($decision->reason);
        }
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->state->isRecyclePending()) {
            return;
        }

        $res = $event->getResponse();
        $res->headers->set('X-Runtime-Recycle', '1');

        // Encourage clients/LB to close connections; helps drain when keep-alive exists.
        if (!$res->headers->has('Connection')) {
            $res->headers->set('Connection', 'close');
        }
    }

    public function onTerminate(TerminateEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $decision = $this->policy->decide($this->state);
        if (!$decision->recycle) {
            return;
        }

        // Optional drain window: wait a little bit before exit (best-effort).
        if ($this->state->isDrainActive()) {
            $deadline = $this->state->getDrainDeadlineAtFloat();
            while (microtime(true) < $deadline) {
                usleep(50_000);
            }
        }

        $this->terminator->terminate($decision->reason);
    }
}
