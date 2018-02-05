<?php
declare(strict_types=1);

namespace App\EventListener\Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class JsonOutputExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event) : ?JsonResponse
    {
        if ('OPTIONS' === $event->getRequest()->getMethod()) {
            return null;
        }

        if ('json' !== $event->getRequest()->getContentType()
            && 'application/vnd.payum+json' !== $event->getRequest()->headers->get('Accept')
        ) {
            return null;
        }

        return new JsonResponse(
            [
                'exception' => get_class($event->getException()),
                'message' => $event->getException()->getMessage(),
                'code' => $event->getException()->getCode(),
                'file' => $event->getException()->getFile(),
                'line' => $event->getException()->getLine(),
                'stackTrace' => $event->getException()->getTraceAsString(),
            ],
            200,
            [
                'Content-Type' => 'application/vnd.payum+json',
            ]
        );
    }
}
