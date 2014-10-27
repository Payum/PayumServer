<?php
namespace Payum\Server\Provider;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Reply\ReplyInterface;
use Payum\Server\Controller\PayumController;
use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;

class PayumProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['payum.security.token_storage'] = $app->share(function() {
            throw new \LogicException('This service has to be overwritten if you would like to use security feature. It must return instance of StorageInterface');
        });

        $app['payum.reply_to_symfony_response_converter'] = $app->share(function($app) {
            return new ReplyToSymfonyResponseConverter();
        });

        $app['payum.security.http_request_verifier'] = $app->share(function($app) {
            return new HttpRequestVerifier($app['payum.security.token_storage']);
        });

        $app['payum.security.token_factory'] = $app->share(function($app) {
            return new TokenFactory(
                $app['url_generator'],
                $app['payum.security.token_storage'],
                $app['payum'],
                'capture',
                'notify',
                'authorize'
            );
        });

        $app['payum.payments'] = $app->share(function () {
            return [];
        });

        $app['payum.storages'] = $app->share(function ($app) {
            return [];
        });

        $app['payum'] = $app->share(function($app) {
            return new SimpleRegistry($app['payum.payments'], $app['payum.storages'], null, null);
        });

        $app['controller.payum'] = $app->share(function() use ($app) {
            return new PayumController(
                $app['payum.security.token_factory'],
                $app['payum.security.http_request_verifier'],
                $app['payum']
            );
        });

        $app->get('/capture/{payum_token}', 'controller.payum:captureAction')->bind('capture');
        $app->post('/capture/{payum_token}', 'controller.payum:captureAction')->bind('capture_post');
        $app->get('/authorize/{payum_token}', 'controller.payum:authorizeAction')->bind('authorize');
        $app->post('/authorize/{payum_token}', 'controller.payum:authorizeAction')->bind('authorize_post');
        $app->get('/notify/{payum_token}', 'controller.payum:notifyAction')->bind('notify');
        $app->post('/notify/{payum_token}', 'controller.payum:notifyAction')->bind('notify_post');

        $app->error(function (\Exception $e, $code) use ($app) {
            if (false == $e instanceof ReplyInterface) {
                return;
            }

            /** @var ReplyToSymfonyResponseConverter $converter */
            $converter = $app['payum.reply_to_symfony_response_converter'];

            return $converter->convert($e);
        }, $priority = 100);
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
