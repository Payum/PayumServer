<?php
namespace Payum\Server\Schema;

use Payum\Server\Schema\Controller\GatewaySchemaController;
use Payum\Server\Schema\Controller\PaymentSchemaController;
use Payum\Server\Schema\Controller\TokenSchemaController;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;

class SchemaProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['payum.schema.controller.gateways'] = $app->share(function() use ($app) {
            return new GatewaySchemaController($app['payum.gateway_schema_builder'], $app['payum.gateway_form_definition_builder']);
        });

        $app['payum.schema.controller.payments'] = $app->share(function() use ($app) {
            return new PaymentSchemaController($app['payum.payment_schema_builder'], $app['payum.payment_form_definition_builder']);
        });

        $app['payum.schema.controller.tokens'] = $app->share(function() use ($app) {
            return new TokenSchemaController($app['payum.token_schema_builder']);
        });

        $app['payum.gateway_schema_builder'] = $app->share(function() use ($app) {
            return new GatewaySchemaBuilder($app['payum']);
        });

        $app['payum.gateway_form_definition_builder'] = $app->share(function() use ($app) {
            return new GatewayFormDefinitionBuilder($app['payum']);
        });

        $app['payum.payment_schema_builder'] = $app->share(function() use ($app) {
            return new PaymentSchemaBuilder($app['payum.gateway_config_storage']);
        });

        $app['payum.payment_form_definition_builder'] = $app->share(function() use ($app) {
            return new PaymentFormDefinitionBuilder($app['payum.gateway_config_storage']);
        });

        $app['payum.token_schema_builder'] = $app->share(function() use ($app) {
            return new TokenSchemaBuilder();
        });

        /** @var ControllerCollection $schema */
        $schema = $app['controllers_factory'];
        $schema->get('/gateways/default.json', 'payum.schema.controller.gateways:getDefaultAction');
        $schema->get('/gateways/form/default.json', 'payum.schema.controller.gateways:getDefaultFormAction');
        $schema->get('/gateways/{name}.json', 'payum.schema.controller.gateways:getAction');
        $schema->get('/gateways/form/{name}.json', 'payum.schema.controller.gateways:getFormAction');

        $schema->get('/payments/new.json', 'payum.schema.controller.payments:getNewAction');
        $schema->get('/payments/form/new.json', 'payum.schema.controller.payments:getNewFormAction');

        $schema->get('/tokens/new.json', 'payum.schema.controller.tokens:getNewAction');

        $app->mount('/schema', $schema);
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
