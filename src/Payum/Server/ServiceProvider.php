<?php
namespace Payum\Server;

use Payum\AuthorizeNet\Aim\AuthorizeNetAimGatewayFactory;
use Payum\Be2Bill\Be2BillDirectGatewayFactory;
use Payum\Be2Bill\Be2BillOffsiteGatewayFactory;
use Payum\Offline\OfflineGatewayFactory;
use Payum\Payex\PayexGatewayFactory;
use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;
use Payum\Paypal\ProCheckout\Nvp\PaypalProCheckoutGatewayFactory;
use Payum\Server\Factory\Storage\DoctrineMongoDbFactory;
use Payum\Server\Form\Type\CreatePaymentType;
use Payum\Server\Form\Type\UpdatePaymentType;
use Payum\Stripe\StripeCheckoutGatewayFactory;
use Payum\Stripe\StripeJsGatewayFactory;
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
        $app['payum.storages_config'] = $app->share(function($app) {
            return [
                'Payum\Server\Model\Payment' => array(
                    'idProperty' => 'number',
                    'factory' => 'doctrine_mongodb',
                    'options' => array(
                        'host' => 'localhost:27017',
                        'databaseName' => 'payum_server',
                    ),
                ),
                'Payum\Server\Model\SecurityToken' => array(
                    'idProperty' => 'hash',
                    'factory' => 'doctrine_mongodb',
                    'options' => array(
                        'host' => 'localhost:27017',
                        'databaseName' => 'payum_server',
                    ),
                ),
                'Payum\Server\Model\GatewayConfig' => array(
                    'idProperty' => 'id',
                    'factory' => 'doctrine_mongodb',
                    'options' => array(
                        'host' => 'localhost:27017',
                        'databaseName' => 'payum_server',
                    ),
                )
            ];
        });

        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new CreatePaymentType();
            $types[] = new UpdatePaymentType();

            return $types;
        }));

        $app['payum.security.token_storage'] = $app->share(function($app) {
            return $app['payum.storages']['Payum\Server\Model\SecurityToken'];
        });

        $app['payum.gateway_config_storage'] = $app->share(function($app) {
            return $app['payum.storages']['Payum\Server\Model\GatewayConfig'];
        });

        $app['payum.authorize_net_aim.gateway_factory'] = $app->share(function ($app) {
            return new AuthorizeNetAimGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.be2bill_direct.gateway_factory'] = $app->share(function ($app) {
            return new Be2BillDirectGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.be2bill_offsite.gateway_factory'] = $app->share(function ($app) {
            return new Be2BillOffsiteGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.offline.gateway_factory'] = $app->share(function ($app) {
            return new OfflineGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.payex.gateway_factory'] = $app->share(function ($app) {
            return new PayexGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.paypal_express_checkout.gateway_factory'] = $app->share(function ($app) {
            return new PaypalExpressCheckoutGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.paypal_pro_checkout.gateway_factory'] = $app->share(function ($app) {
            return new PaypalProCheckoutGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.stripe_checkout.gateway_factory'] = $app->share(function ($app) {
            return new StripeCheckoutGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.stripe_js.gateway_factory'] = $app->share(function ($app) {
            return new StripeJsGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.stripe_direct.gateway_factory'] = $app->share(function ($app) {
            return new StripeJsGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.gateway_choices'] = $app->share(function ($app) {
            return array(
                'authorize_net_aim' => 'Authorize.NET AIM',
                'be2bill_direct' => 'Be2Bill Direct',
                'be2bill_offsite' => 'Be2Bill Offsite',
                'offline' => 'offline',
                'payex' => 'Payex Offsite',
                'paypal_express_checkout' => 'Paypal ExpressCheckout',
                'paypal_pro_checkout' => 'Paypal ProCheckout',
                'stripe_checkout' => 'Stripe Checkout',
                'stripe_js' => 'Stripe.Js',
                'stripe_direct' => 'Stripe Direct',
            );
        });

        $app['payum.stripe_direct.gateway_factory'] = $app->share(function ($app) {
            return new StripeJsGatewayFactory([], $app['payum.core_gateway_factory']);
        });

        $app['payum.gateway_factories'] = $app->share(function ($app) {
            $factories = array();

            // TODO: whenever you add a factory here, add it to choices list too
            $factories['authorize_net_aim'] = 'payum.authorize_net_aim.gateway_factory';
            $factories['be2bill_direct'] = 'payum.be2bill_direct.gateway_factory';
            $factories['be2bill_offsite'] = 'payum.be2bill_offsite.gateway_factory';
            $factories['offline'] = 'payum.offline.gateway_factory';
            $factories['payex'] = 'payum.payex.gateway_factory';
            $factories['paypal_express_checkout'] = 'payum.paypal_express_checkout.gateway_factory';
            $factories['paypal_pro_checkout'] = 'payum.paypal_pro_checkout.gateway_factory';
            $factories['stripe_checkout'] = 'payum.stripe_checkout.gateway_factory';
            $factories['stripe_js'] = 'payum.stripe_js.gateway_factory';
            $factories['stripe_direct'] = 'payum.stripe_direct.gateway_factory';

            return $factories;
        });

        $app['payum.storage_factories'] = $app->share(function ($app) {
            $factories = array();

            $factory = new DoctrineMongoDbFactory();
            $factories[$factory->getName()] = $factory;

            return $factories;
        });

        $app['payum.storages'] = $app->share(function ($app) {
            /** @var \Payum\Server\Factory\Storage\FactoryInterface[] $factories */
            $factories = $app['payum.storage_factories'];

            $storages = array();
            foreach ($app['payum.storages_config'] as $modelClass => $config) {
                $storages[$modelClass] = $factories[$config['factory']]->createStorage(
                    $modelClass,
                    $config['idProperty'],
                    $config['options']
                );
            }

            return $storages;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
    }
}
