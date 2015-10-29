<?php
namespace Payum\Server\Api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RootController
{
    public function rootAction()
    {
        return new JsonResponse([
            'name' => 'PayumServer',
            'version' => '1.0.x',
            'tagline' => 'Payment processing server. Setup once and rule them all',
        ]);
    }
}
