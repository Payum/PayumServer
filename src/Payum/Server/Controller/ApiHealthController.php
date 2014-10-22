<?php
namespace Payum\Server\Controller;

use Symfony\Component\HttpFoundation\Response;

class ApiHealthController
{
    public function checksAction()
    {
        return new Response('', 204);
    }
}
