<?php
namespace Payum\Server;

use Doctrine\MongoDB\Connection;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\PayumBuilder;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Form\Type\CreatePaymentType;
use Payum\Server\Form\Type\UpdatePaymentType;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Model\Payment;
use Payum\Server\Model\SecurityToken;
use Payum\Server\Storage\MongoStorage;
use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['debug'] = (boolean) getenv('PAYUM_SERVER_DEBUG');

        $app['payum.gateway_config_storage'] = $app->share(function ($app) {
            /** @var Connection $connection */
            $connection = $app['doctrine.mongo.connection'];
            $db = $connection->selectDatabase('payum_server');

            return new MongoStorage(GatewayConfig::class, $db->selectCollection('gateway_configs'));
        });

        $app['payum.builder'] = $app->share($app->extend('payum.builder', function (PayumBuilder $builder) use ($app) {
            /** @var Connection $connection */
            $connection = $app['doctrine.mongo.connection'];
            $db = $connection->selectDatabase('payum_server');

            $builder
                ->setTokenStorage(new MongoStorage(SecurityToken::class, $db->selectCollection('security_tokens')))
                ->setGatewayConfigStorage($app['payum.gateway_config_storage'])
                ->addStorage(Payment::class, new MongoStorage(Payment::class, $db->selectCollection('payments')))
            ;


            return $builder;
        }));

        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new CreatePaymentType();
            $types[] = new UpdatePaymentType();

            return $types;
        }));

        $app['payum.reply_to_json_response_converter'] = $app->share(function ($app) {
            return new ReplyToJsonResponseConverter();
        });

        $app['doctrine.mongo.connection'] = $app->share(function ($app) {
            return new Connection();
        });

        $app['payum.gateway_choices'] = $app->extend('payum.gateway_choices', function (array $choices) use ($app) {
            /** @var StorageInterface $gatewayConfigStorage */
            $gatewayConfigStorage = $app['payum.gateway_config_storage'];
            foreach ($gatewayConfigStorage->findBy([]) as $config) {
                /** @var GatewayConfigInterface $config */

                $choices[$config->getGatewayName()] = ucwords(str_replace(['_'], ' ', $config->getGatewayName()));
            }

            return $choices;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
