<?php
declare(strict_types=1);

namespace App\EventListener\Response;

use Payum\Core\Reply\HttpResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\Router;

class VndHeaderListener
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(Router $router, ContainerInterface $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    private function getRoutes() : array
    {
        return [
            $this->container->getParameter('payum.capture_path'),
            $this->container->getParameter('payum.notify_path'),
            $this->container->getParameter('payum.authorize_path'),
            $this->container->getParameter('payum.refund_path'),
            $this->container->getParameter('payum.cancel_path'),
            $this->container->getParameter('payum.payout_path'),
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event) : void
    {
        $route = $event->getRequest()->get('_route');

        if (!in_array($route, $this->getRoutes())) {
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
