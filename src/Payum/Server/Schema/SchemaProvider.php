<?php
namespace Payum\Server\Schema;

use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\GatewayConfigToJsonConverter;
use Payum\Server\Api\View\PaymentToJsonConverter;
use Payum\Server\Api\View\TokenToJsonConverter;
use Payum\Server\Application;
use Payum\Server\Schema\Controller\GatewaySchemaController;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SchemaProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['payum.schema.controller.gateways'] = $app->share(function() use ($app) {
            return new GatewaySchemaController($app['payum.schema_builder'], $app['payum.form_definition_builder']);
        });

        $app['payum.schema_builder'] = $app->share(function() use ($app) {
            return new SchemaBuilder($app['payum']);
        });

        $app['payum.form_definition_builder'] = $app->share(function() use ($app) {
            return new FormDefinitionBuilder($app['payum']);
        });

        /** @var ControllerCollection $schema */
        $schema = $app['controllers_factory'];
        $schema->get('/gateways/default.json', 'payum.schema.controller.gateways:getDefaultAction');
        $schema->get('/gateways/form/default.json', 'payum.schema.controller.gateways:getDefaultFormAction');
        $schema->get('/gateways/{name}.json', 'payum.schema.controller.gateways:getAction');
        $schema->get('/gateways/form/{name}.json', 'payum.schema.controller.gateways:getFormAction');

        $app->mount('/schema', $schema);
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
