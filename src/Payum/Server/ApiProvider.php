<?php
namespace Payum\Server;

use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\OrderToJsonConverter;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ApiProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['api.view.order_to_json_converter'] = function() {
            return new OrderToJsonConverter();
        };

        $app['api.view.form_to_json_converter'] = function() {
            return new FormToJsonConverter();
        };
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
    }
}
