<?php
declare(strict_types=1);

namespace App\EventListener\Response;

use Payum\Core\Reply\HttpResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\Router;

class VndHeaderListener
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    private $routes;

    public function __construct(Router $router, array $routes)
    {
        $this->router = $router;
        $this->routes = $routes;
    }

    public function onKernelResponse(FilterResponseEvent $event) : void
    {
        $route = $event->getRequest()->get('_route');

        if (!in_array($route, $this->routes)) {
            return;
        }

        if ('OPTIONS' === $event->getRequest()->getMethod()) {
            return;
        }

        if ('application/vnd.payum+json' === $event->getResponse()->headers->get('Content-Type')) {
            return;
        }

        if ('application/json' === $event->getResponse()->headers->get('Content-Type')) {
            return;
        }

        if ('application/vnd.payum+json' === $event->getResponse()->headers->get('Accept')) {
            throw new HttpResponse($event->getResponse());
        }
    }
}
