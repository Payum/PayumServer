<?php
namespace Payum\Server\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Server\Model\Order;
use Payum\Server\Request\ProtectedDetailsRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiOrderController
{
    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

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
    protected $orderClass;

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     * @param RegistryInterface $registry
     * @param string $orderClass
     */
    public function __construct(
        GenericTokenFactoryInterface $tokenFactory,
        HttpRequestVerifierInterface $httpRequestVerifier,
        RegistryInterface $registry,
        $orderClass
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->registry = $registry;
        $this->orderClass = $orderClass;
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

        $storage = $this->registry->getStorageForClass($this->orderClass, $name);

        /** @var Order $order */
        $order = $storage->createModel();
        $order->setAmount($rawDetails['amount']);
        $order->setCurrency($rawDetails['currency']);

        $storage->updateModel($order);

        $captureToken = $this->tokenFactory->createCaptureToken($name, $order, $afterUrl);
        $getToken = $this->tokenFactory->createToken($name, $order, 'order_get');

        $meta = array();
        $meta['links'] = array(
            'purchase' => null,
            'get' => $getToken->getTargetUrl(),
        );
        $order->setMeta($meta);

        $storage->updateModel($order);

        $meta = $order->getMeta();
        $meta['links']['purchase'] = $captureToken->getTargetUrl();
        $order->setMeta($meta);

        $response = new JsonResponse(iterator_to_array($order));
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

        /** @var Order $order */
        $order = $status->getModel();
        $meta = $order->getMeta();
        $meta['status'] = $status->getStatus();
        $order->setMeta($meta);

        $storage = $this->registry->getStorageForClass($this->orderClass, $token->getPaymentName());
        $storage->updateModel($order);

        return new JsonResponse(iterator_to_array($order));
    }
}
