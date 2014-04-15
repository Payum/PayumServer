<?php
namespace Payum\Server;

use Buzz\Client\Curl;
use Omnipay\Common\GatewayFactory;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\OmnipayBridge\PaymentFactory as OmnipayPaymentFactory;
use Payum\Server\Action\GetSensitiveValuesAction;
use Payum\Server\Action\Stripe\OmnipayStripeCaptureAction;
use Payum\Server\Action\Stripe\ProtectDetailsAction as StripeProtectedDetailsAction;
use Payum\Server\Action\Paypal\PaypalExpressCheckoutCaptureAction;
use Payum\Server\Action\VoidProtectDetailsAction;
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
        $app['payum.model.security_token_class'] = 'Payum\Server\Model\SecurityToken';

        $app['payum.security.token_storage'] = $app->share(function($app) {
            return new FilesystemStorage(
                $app['payum.storage_dir'],
                $app['payum.model.security_token_class'],
                'hash'
            );
        });

        $app['payum.security.http_request_verifier'] = $app->share(function($app) {
            return new HttpRequestVerifier($app['payum.security.token_storage']);
        });

        $app['payum.security.token_factory'] = $app->share(function($app) {
            return new TokenFactory(
                $app['url_generator'],
                $app['payum.security.token_storage'],
                $app['payum'],
                'purchase',
                'notify'
            );
        });

        $app['payum'] = $app->share(function($app) {
            $config = $app['payum.config'];

            $detailsClass = $app['payum.model.payment_details_class'];

            $stripeGateway = GatewayFactory::create('Stripe');
            $stripeGateway->setApiKey($config['stripe']['secret_key']);
            $stripeGateway->setTestMode($config['stripe']['sandbox']);

            $storages = array(
                'paypal' => array(
                    $detailsClass => new FilesystemStorage($app['payum.storage_dir'], $detailsClass, 'id')
                ),
                'stripe' => array(
                    $detailsClass => new FilesystemStorage($app['payum.storage_dir'], $detailsClass, 'id')
                )
            );


            $payments = array(
                'paypal' => PaymentFactory::create(new Api(new Curl, array(
                    'username' => $config['paypal']['username'],
                    'password' => $config['paypal']['password'],
                    'signature' => $config['paypal']['signature'],
                    'sandbox' => $config['paypal']['sandbox']
                ))),
                'stripe' => OmnipayPaymentFactory::create($stripeGateway)
            );

            $payments['paypal']->addAction(new PaypalExpressCheckoutCaptureAction($app), true);
            $payments['paypal']->addAction(new VoidProtectDetailsAction, true);

            $payments['stripe']->addAction(new OmnipayStripeCaptureAction, true);
            $payments['stripe']->addAction(new StripeProtectedDetailsAction, true);
            $payments['stripe']->addAction(new GetSensitiveValuesAction($app['request']), true);

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