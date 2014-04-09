<?php
namespace Payum\Server\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\SensitiveValue;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Request\GetSensitiveKeysRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiPaymentController
{
    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @var StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var HttpRequestVerifierInterface
     */
    protected $httpRequestVerifier;

    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var string
     */
    protected $paymentClass;

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     * @param StorageInterface $tokenStorage
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     * @param RegistryInterface $registry
     * @param string $paymentClass
     */
    public function __construct(
        GenericTokenFactoryInterface $tokenFactory,
        StorageInterface $tokenStorage,
        HttpRequestVerifierInterface $httpRequestVerifier,
        RegistryInterface $registry,
        $paymentClass
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->tokenStorage = $tokenStorage;
        $this->registry = $registry;
        $this->paymentClass = $paymentClass;
        $this->httpRequestVerifier = $httpRequestVerifier;
    }

    /**
     * @param Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        if ('json' !== $request->getContentType()) {
            throw new BadRequestHttpException('The request content type is invalid.');
        }

        $rawDetails = json_decode($request->getContent(), true);
        if (null ===  $rawDetails) {
            throw new BadRequestHttpException('The request content is not valid json.');
        }
        if (empty($rawDetails['meta']['name'])) {
            throw new BadRequestHttpException('The payment name must be set to meta.name.');
        }
        $name = $rawDetails['meta']['name'];

        if (empty($rawDetails['meta']['purchase_after_url'])) {
            throw new BadRequestHttpException('The purchase after url has to be set to  meta.purchase_after_url.');
        }
        $afterUrl = $rawDetails['meta']['purchase_after_url'];

        $storage = $this->registry->getStorageForClass($this->paymentClass, $name);

        $details = $storage->createModel();
        ArrayObject::ensureArrayObject($details)->replace($rawDetails);

        $sensitiveKeys = new GetSensitiveKeysRequest;
        $this->registry->getPayment($name)->execute($sensitiveKeys);

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
        $captureToken = $this->tokenFactory->createToken($name, $details, 'purchase', $purchaseParameters);
        $captureToken->setAfterUrl($afterUrl);
        $this->tokenStorage->updateModel($captureToken);

        $getToken = $this->tokenFactory->createToken($name, $details, 'payment_get');

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
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $token = $this->httpRequestVerifier->verify($request);

        $status = new BinaryMaskStatusRequest($token);
        $this->registry->getPayment($token->getPaymentName())->execute($status);

        $details = $status->getModel();
        $meta = $details['meta'];
        $meta['status'] = $status->getStatus();
        $details['meta'] = $meta;

        $storage = $this->registry->getStorageForClass($this->paymentClass, $token->getPaymentName());
        $storage->updateModel($details);

        return new JsonResponse(iterator_to_array($details));
    }
}
