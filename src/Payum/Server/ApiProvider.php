<?php
namespace Payum\Server;

use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\OrderToJsonConverter;
use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;

class ApiProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['api.view.order_to_json_converter'] = function() use ($app) {
            return new OrderToJsonConverter($app['payum']);
        };

        $app['api.view.form_to_json_converter'] = function() {
            return new FormToJsonConverter();
        };
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
