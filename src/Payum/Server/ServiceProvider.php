<?php
namespace Payum\Server;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\PaymentInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Server\Factory\Payment\FactoryInterface;
use Payum\Server\Factory\Payment\PaypalExpressCheckoutFactory;
use Payum\Server\Factory\Payment\StripeCheckoutFactory;
use Payum\Server\Factory\Payment\StripeJsFactory;
use Payum\Server\Factory\Storage\DoctrineMongoODMFactory;
use Payum\Server\Factory\Storage\FilesystemFactory;
use Payum\Server\Form\Type\CreateOrderType;
use Payum\Server\Form\Type\CreatePaymentConfigType;
use Payum\Server\Form\Type\CreateStorageConfigType;
use Payum\Server\Form\Type\UpdateOrderType;
use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(SilexApplication $app)
    {
        $app['debug'] = (boolean) getenv('PAYUM_SERVER_DEBUG');
        $app['payum.config_file'] = $app->share(function($app) {
            return $app['payum.root_dir'].'/payum.yml';
        });
        $app['payum.config'] = $app->share(function($app) {
            return file_exists($app['payum.config_file']) ?
                Yaml::parse(file_get_contents($app['payum.config_file'])) :
                array('payments' => array(), 'storages' => array())
            ;
        });
        $app['payum.storage_dir'] = $app->share(function($app) {
            return $app['app.root_dir'].'/storage';
        });
        $app['payum.model.order_class'] = 'Payum\Server\Model\Order';
        $app['payum.model.order_id_property'] = 'number';
        $app['payum.model.security_token_class'] = 'Payum\Server\Model\SecurityToken';
        $app['payum.model.security_token_id_property'] = 'hash';

        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new CreatePaymentConfigType($app['payum.payment_factories']);
            $types[] = new CreateStorageConfigType($app['payum.storage_factories']);
            $types[] = new CreateOrderType($app['payum.config']);
            $types[] = new UpdateOrderType();

            return $types;
        }));

        $app['payum.security.token_storage'] = $app->share(function($app) {
            return $app['payum.storages']['Payum\Server\Model\SecurityToken'];
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

        $app['payum.payment_factories'] = $app->share(function ($app) {
            $factories = array();

            $factory = new PaypalExpressCheckoutFactory;
            $factories[$factory->getName()] = $factory;

            $factory = new StripeJsFactory;
            $factories[$factory->getName()] = $factory;

            $factory = new StripeCheckoutFactory;
            $factories[$factory->getName()] = $factory;

            return $factories;
        });

        $app['payum.payments'] = $app->share(function ($app) {
            $config = $app['payum.config'];

            /** @var FactoryInterface[] $factories */
            $factories = $app['payum.payment_factories'];

            /** @var PaymentInterface[] $payments */
            $payments = array();
            foreach ($config['payments'] as $name => $paymentConfig) {
                if (false == array_key_exists($paymentConfig['factory'], $factories)) {
                    throw new \LogicException(sprintf(
                        'There is not such factory: %s. Cannot create a payment',
                        $paymentConfig['factory']
                    ));
                }

                $payments[$name] = $factories[$paymentConfig['factory']]->createPayment($paymentConfig['options']);
            }

            return $payments;
        });

        $app['payum.storage_factories'] = $app->share(function ($app) {
            $factories = array();

            $factory = new FilesystemFactory($app['app.root_dir']);
            $factories[$factory->getName()] = $factory;

            $factory = new DoctrineMongoODMFactory($app['app.root_dir']);
            $factories[$factory->getName()] = $factory;

            return $factories;
        });

        $app['payum.storages'] = $app->share(function ($app) {
            $config = $app['payum.config']['storages'];

            /** @var \Payum\Server\Factory\Storage\FactoryInterface[] $factories */
            $factories = $app['payum.storage_factories'];

            $storages = array(
                $config['order']['modelClass'] => $factories[$config['order']['factory']]->createStorage(
                    $config['order']['modelClass'],
                    $config['order']['idProperty'],
                    $config['order']['options']
                ),
                $config['security_token']['modelClass'] => $factories[$config['security_token']['factory']]->createStorage(
                    $config['security_token']['modelClass'],
                    $config['security_token']['idProperty'],
                    $config['security_token']['options']
                ),
            );

            return $storages;
        });

        $app['payum'] = $app->share(function($app) {
            return new SimpleRegistry($app['payum.payments'], $app['payum.storages'], null, null);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(SilexApplication $app)
    {
        $config = $app['payum.config'];

        if (false == isset($config['storages']['order'])) {
            $config['storages']['order'] = array(
                'modelClass' => 'Payum\Server\Model\Order',
                'idProperty' => 'number',
                'factory' => 'filesystem',
                'options' => array(
                    'storageDir' => '%app.root_dir%/storage',
                ),
            );

            file_put_contents($app['payum.config_file'], Yaml::dump($config, 5));
        }

        if (false == isset($config['storages']['security_token'])) {
            $config['storages']['security_token'] = array(
                'modelClass' => 'Payum\Server\Model\SecurityToken',
                'idProperty' => 'hash',
                'factory' => 'filesystem',
                'options' => array(
                    'storageDir' => '%app.root_dir%/storage',
                ),
            );

            file_put_contents($app['payum.config_file'], Yaml::dump($config, 5));
        }

        $app['payum.config'] = $config;
    }
}
