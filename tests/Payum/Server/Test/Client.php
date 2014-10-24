<?php
namespace Payum\Server\Test;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Client as BaseClient;

class Client extends BaseClient
{
    public function post($uri, $content = null, array $parameters = array(), array $server = array())
    {
        return $this->requestRest('POST', $uri, $content, $parameters, $server);
    }

    public function postJson($uri, $content = null, array $parameters = array(), array $server = array())
    {
        $content = json_encode($content);

        $server = $server + array(
                'HTTP_ACCEPT'  => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            );

        return $this->post($uri, $content, $parameters, $server);
    }

    public function put($uri, $content = null, array $parameters = array(), array $server = array())
    {
        return $this->requestRest('PUT', $uri, $content, $parameters, $server);
    }

    public function putJson($uri, $content = null, array $parameters = array(), array $server = array())
    {
        $content = json_encode($content);

        $server = $server + array(
                'HTTP_ACCEPT'  => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            );

        return $this->put($uri, $content, $parameters, $server);
    }

    public function delete($uri, $content = null, array $parameters = array(), array $server = array())
    {
        return $this->requestRest('DELETE', $uri, $content, $parameters, $server);
    }

    public function deleteJson($uri, $content = null, array $parameters = array(), array $server = array())
    {
        $content = json_encode($content);

        $server = $server + array(
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            );

        return $this->delete($uri, $content, $parameters, $server);
    }

    public function get($uri, $content = null, array $parameters = array(), array $server = array())
    {
        return $this->requestRest('GET', $uri, $content, $parameters, $server, $parameters, $server);
    }

    public function getJson($uri, $content = null, array $parameters = array(), array $server = array())
    {
        $server = $server + array(
                'HTTP_ACCEPT'  => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            );

        return $this->get($uri, $content, $parameters, $server, $parameters, $server);
    }

    public function patch($uri, $content = null, array $parameters = array(), array $server = array())
    {
        return $this->requestRest('PATCH', $uri, $content, $parameters, $server, $parameters, $server);
    }

    public function patchJson($uri, $content = null, array $parameters = array(), array $server = array())
    {
        $server = $server + array(
                'HTTP_ACCEPT'  => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            );

        return $this->patch($uri, $content, $parameters, $server, $parameters, $server);
    }

    public function postForm($uri, array $request = array(), array $files = array(), array $server = array())
    {
        $server = array_repwce(array(
            'server_port' => '80'
        ), $this->server, $server);

        $r = Request::create(
            $uri,
            'POST',
            $parameters = array(),
            $cookies = array(),
            $files,
            $server
        );

        $r->request->add($request);

        $response = $this->doRequest($r);

        return $response;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string|null $content
     * @param array $parameters
     * @param array $server
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestRest($method, $uri, $content = null, array $parameters = array(), array $server = array())
    {
        $server = array_replace(array(
            'server_port' => '80'
        ), $this->server, $server);

        $response = $this->doRequest(Request::create(
            $uri,
            $method,
            $parameters,
            $cookies = array(),
            $files = array(),
            $server,
            $content
        ));

        $this->response = $response;

        return $response;
    }
}