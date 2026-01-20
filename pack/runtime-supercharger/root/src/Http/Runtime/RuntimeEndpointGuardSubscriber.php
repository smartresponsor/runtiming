<?php
declare(strict_types=1);



namespace App\Http\Runtime;

use App\ServiceInterface\Runtime\RuntimeEndpointGuardInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RuntimeEndpointGuardSubscriber implements EventSubscriberInterface
{
    private RuntimeEndpointGuardInterface $guard;

    public function __construct(RuntimeEndpointGuardInterface $guard)
    {
        $this->guard = $guard;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 60],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $result = $this->guard->check($event->getRequest());
        if ($result->allowed) {
            return;
        }

        $event->setResponse(new JsonResponse([
            'error' => 'forbidden',
            'code' => 'runtime_endpoint_denied',
            'reason' => $result->reason,
        ], 403));
    }
}
