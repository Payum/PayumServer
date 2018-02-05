<?php
declare(strict_types=1);

namespace App\Test;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Client as BaseClient;
use Symfony\Component\HttpFoundation\Response;

class Client extends BaseClient
{
    public function post($uri, $content = null, array $parameters = [], array $server = [])
    {
        return $this->requestRest('POST', $uri, $content, $parameters, $server);
    }

    public function postJson($uri, $content = null, array $parameters = [], array $server = [])
    {
        $content = json_encode($content);

        $server = $server + [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ];

        return $this->post($uri, $content, $parameters, $server);
    }

    public function requestRest(
        string $method,
        string $uri,
        ?string $content,
        array $parameters = [],
        array $server = []
    ) : Response {
        $server = array_replace([
            'server_port' => '80',
        ], $this->server, $server);

        $response = $this->doRequest(Request::create(
            $uri,
            $method,
            $parameters,
            $cookies = [],
            $files = [],
            $server,
            $content
        ));

        $this->response = $response;

        return $response;
    }
}
