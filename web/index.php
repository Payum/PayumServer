<?php
namespace Payum\Server;

require_once __DIR__.'/../vendor/autoload.php';

use Buzz\Client\Curl;
use Omnipay\Common\GatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Request\RedirectUrlInteractiveRequest;
use Payum\Server\Request\SecuredCaptureRequest;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\SensitiveValue;
use Payum\Core\Security\TokenInterface;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Server\Action\GetSensitiveValuesAction;
use Payum\Server\Action\OmnipayStripeCaptureAction;
use Payum\Server\Action\OmnipayStripeSensitiveKeysAction;
use Payum\Server\Action\PaypalExpressCheckoutCaptureAction;
use Payum\Server\Action\VoidGetSensitiveKeysAction;
use Payum\Server\Request\GetSensitiveKeysRequest;
use Payum\OmnipayBridge\PaymentFactory as OmnipayPaymentFactory;
use Silex\Application;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

$app = new Application;
$app['debug'] = true;
$app['payum.config'] = Yaml::parse(file_get_contents(__DIR__.'/../payum.yml'));
$app['payum.storage_dir'] = __DIR__.'/../storage';
$app['payum.model.payment_details_class'] = 'Payum\Server\Model\PaymentDetails';
$app['payum.model.security_token_class'] = 'Payum\Server\Model\SecurityToken';

$app->register(new UrlGeneratorServiceProvider());

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

    $payments['paypal']->addAction(new PaypalExpressCheckoutCaptureAction, true);
    $payments['paypal']->addAction(new VoidGetSensitiveKeysAction, true);

    $payments['stripe']->addAction(new OmnipayStripeCaptureAction, true);
    $payments['stripe']->addAction(new OmnipayStripeSensitiveKeysAction, true);
    $payments['stripe']->addAction(new GetSensitiveValuesAction($app['request']), true);

    return new SimpleRegistry($payments, $storages, null, null);
});

$app->get('/', function () {
    return MarkdownExtended(file_get_contents(__DIR__.'/../README.md'));
});

$app->get('/purchase/{payum_token}', function (Application $app, Request $request) {
    /** @var TokenInterface $token */
    $token = $app['payum.security.http_request_verifier']->verify($request);

    /** @var RegistryInterface $payum */
    $payum = $app['payum'];

    try {
        $payment = $payum->getPayment($token->getPaymentName());
        $payment->execute(new SecuredCaptureRequest($token));
    } catch (RedirectUrlInteractiveRequest $e) {
        return $app->redirect($e->getUrl());
    }

    $app['payum.security.http_request_verifier']->invalidate($token);

    return $app->redirect($token->getAfterUrl());
})->bind('purchase');

$app->post('/api/payment', function (Application $app, Request $request) {
    if ('json' !== $request->getContentType()) {
        $app->abort(400, 'The request content type is invalid.');
    }

    $rawDetails = json_decode($request->getContent(), true);
    if (null ===  $rawDetails) {
        $app->abort(400, 'The request content is not valid json.');
    }
    if (empty($rawDetails['meta']['name'])) {
        $app->abort(400, 'The payment name must be set to meta.name.');
    }
    $name = $rawDetails['meta']['name'];

    if (empty($rawDetails['meta']['purchase_after_url'])) {
        $app->abort(400, 'The purchase after url has to be set to  meta.purchase_after_url.');
    }
    $afterUrl = $rawDetails['meta']['purchase_after_url'];

    /** @var RegistryInterface $payum */
    $payum = $app['payum'];
    /** @var GenericTokenFactoryInterface $tokenFactory */
    $tokenFactory = $app['payum.security.token_factory'];

    $storage = $payum->getStorageForClass($app['payum.model.payment_details_class'], $name);

    $details = $storage->createModel();
    ArrayObject::ensureArrayObject($details)->replace($rawDetails);

    $sensitiveKeys = new GetSensitiveKeysRequest;
    $payum->getPayment($name)->execute($sensitiveKeys);

    $sensitiveValues = array();
    foreach ($sensitiveKeys->getKeys() as $sensitiveKey) {
        if (isset($details[$sensitiveKey])) {
            $sensitiveValues[$sensitiveKey] = $details[$sensitiveKey];
            $details[$sensitiveKey] = new SensitiveValue($details[$sensitiveKey]);
        }
    }

    $storage->updateModel($details);

    $purchaseParameters = array_filter(array(
        'sensitive' => base64_encode(json_encode($sensitiveValues))
    ));
    $captureToken = $tokenFactory->createToken($name, $details, 'purchase', $purchaseParameters);
    $captureToken->setAfterUrl($afterUrl);
    $app['payum.security.token_storage']->updateModel($captureToken);

    $getToken = $tokenFactory->createToken($name, $details, 'payment_get');

    $meta = $details['meta'];
    $meta['links'] = array(
        'purchase' => null,
        'get' => $getToken->getTargetUrl(),
    );
    $details['meta'] = $meta;

    $storage->updateModel($details);

    $meta = $details['meta'];
    $meta['links']['purchase'] = $captureToken->getTargetUrl();
    $details['meta'] = $meta;

    $response = new JsonResponse(iterator_to_array($details));
    $response->headers->set('Cache-Control', 'no-store, no-cache, max-age=0, post-check=0, pre-check=0');
    $response->headers->set('Pragma', 'no-cache');

    return $response;
})->bind('payment_create');

$app->get('/api/payment/{payum_token}', function (Application $app, Request $request) {
    /** @var TokenInterface $token */
    $token = $app['payum.security.http_request_verifier']->verify($request);

    /** @var RegistryInterface $payum */
    $payum = $app['payum'];

    $status = new BinaryMaskStatusRequest($token);
    $payum->getPayment($token->getPaymentName())->execute($status);

    $details = $status->getModel();
    $meta = $details['meta'];
    $meta['status'] = $status->getStatus();
    $details['meta'] = $meta;

    $storage = $payum->getStorageForClass($app['payum.model.payment_details_class'], $token->getPaymentName());
    $storage->updateModel($details);

    return new JsonResponse(iterator_to_array($details));
})->bind('payment_get');

$app->run();