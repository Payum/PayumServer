<?php
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../vendor/autoload.php';

// workaround for silex controller collection, force / for route collections.
if ('/payments' == $_SERVER['REQUEST_URI']) {
    $_SERVER['REQUEST_URI'] .= '/';
}
if ('/gateways' == $_SERVER['REQUEST_URI']) {
    $_SERVER['REQUEST_URI'] .= '/';
}
if ('/tokens' == $_SERVER['REQUEST_URI']) {
    $_SERVER['REQUEST_URI'] .= '/';
}

if ($trustedProxy = getenv('TRUSTED_PROXY')) {
    Request::setTrustedProxies([$trustedProxy]);
}

$request = Request::createFromGlobals();

(new \Payum\Server\Application)->run($request);
