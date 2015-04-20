<?php
namespace Payum\Server\Api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RootController
{
    public function rootAction()
    {
        return new JsonResponse([
            'status' => 200,
            'name' => 'PayumServer',
            'version' => '0.15.x',
            'tagline' => 'You Know, for processing payments',
        ]);
    }
}
