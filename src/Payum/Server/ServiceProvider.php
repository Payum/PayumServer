<?php
namespace Payum\Server;

use Omnipay\Omnipay;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\PaymentInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\OmnipayBridge\PaymentFactory as OmnipayPaymentFactory;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $app['debug'] = true;
        $app['payum.config'] = Yaml::parse(file_get_contents($app['app.root_dir'].'/payum.yml'));
        $app['payum.storage_dir'] = $app['app.root_dir'].'/storage';
        $app['payum.model.payment_details_class'] = 'Payum\Server\Model\PaymentDetails';
        $app['payum.model.order_class'] = 'Payum\Server\Model\Order';
        $app['payum.model.security_token_class'] = 'Payum\Server\Model\SecurityToken';

        $app['payum.security.token_storage'] = $app->share(function($app) {
            return new FilesystemStorage(
                $app['payum.storage_dir'],
                $app['payum.model.security_token_class'],
                'hash'
            );
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

        $app['payum'] = $app->share(function($app) {
            $config = $app['payum.config'];

            $orderClass = $app['payum.model.order_class'];

//            $gatewayFactory = Omnipay::getFactory();
//            $gatewayFactory->find();
//
//            $stripeGateway = $gatewayFactory->create('Stripe');
//            $stripeGateway->setApiKey($config['stripe']['secret_key']);
//            $stripeGateway->setTestMode($config['stripe']['sandbox']);
//
            $storages = array(
                $orderClass => new FilesystemStorage($app['payum.storage_dir'], $orderClass, 'id')
            );

            /** @var PaymentInterface[] $payments */
            $payments = array(
                'paypal' => PaymentFactory::create(new Api(array(
                    'username' => $config['paypal']['username'],
                    'password' => $config['paypal']['password'],
                    'signature' => $config['paypal']['signature'],
                    'sandbox' => $config['paypal']['sandbox']
                ))),
//                'stripe' => OmnipayPaymentFactory::create($stripeGateway)
            );

            return new SimpleRegistry($payments, $storages, null, null);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
    }
}