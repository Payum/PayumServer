<?php
namespace Payum\Server\Api;

use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\GatewayConfigToJsonConverter;
use Payum\Server\Api\View\PaymentToJsonConverter;
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
            return new PaymentToJsonConverter($app['payum']);
        };

        $app['api.view.form_to_json_converter'] = function() {
            return new FormToJsonConverter();
        };

        $app['api.view.gateway_config_to_json_converter'] = function() {
            return new GatewayConfigToJsonConverter();
        };
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
