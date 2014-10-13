<?php
namespace Payum\Server\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Request\GetHumanStatus;
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

        $rawOrder = json_decode($request->getContent(), true);
        if (null ===  $rawOrder) {
            throw new BadRequestHttpException('The request content is not valid json.');
        }
        $rawOrder = ArrayObject::ensureArrayObject($rawOrder);

        $storage = $this->registry->getStorage('Payum\Server\Model\Order');

        /** @var Order $order */
        $order = $storage->createModel();
        $order->setPaymentName($rawOrder['paymentName']);
        $order->setAfterUrl($rawOrder['afterUrl'] ?: $request->getSchemeAndHttpHost());
        $order->setNumber(date('Ymd-'.mt_rand(10000, 99999)));
        $order->setTotalAmount($rawOrder['totalAmount']);
        $order->setCurrencyCode($rawOrder['currencyCode']);
        $order->setClientEmail($rawOrder['clientEmail']);
        $order->setClientId($rawOrder['clientId']);
        $order->setDetails(is_array($rawOrder['details']) ? $rawOrder['details'] : array());
        $storage->updateModel($order);

        $response = new JsonResponse($this->buildJsonOrder($order));
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

        $storage = $this->registry->getStorage('Payum\Server\Model\Order');

        /** @var Order $order */
        $order = $storage->findModelByIdentificator($token->getDetails());

        $payment = $this->registry->getPayment($order->getPaymentName());

        $payment->execute($status = new GetHumanStatus($order));

        $order->setPaymentStatus($status->getValue());
        $storage->updateModel($order);

        return new JsonResponse($this->buildJsonOrder($order));
    }

    public function buildJsonOrder(Order $order)
    {
        $getToken = $this->tokenFactory->createToken($order->getPaymentName(), $order, 'order_get');
        $captureToken = $this->tokenFactory->createCaptureToken($order->getPaymentName(), $order, $order->getAfterUrl());
        $authorizeToken = $this->tokenFactory->createAuthorizeToken($order->getPaymentName(), $order, $order->getAfterUrl());
        $notifyToken = $this->tokenFactory->createNotifyToken($order->getPaymentName(), $order);

        return array(
            'order' => array(
                'paymentName' => $order->getPaymentName(),
                'paymentStatus' => $order->getPaymentStatus(),
                'after_url' => $order->getAfterUrl(),
                'number' => $order->getNumber(),
                'totalAmount' => $order->getTotalAmount(),
                'currencyCode' => $order->getCurrencyCode(),
                'clientEmail' => $order->getClientEmail(),
                'clientId' => $order->getClientId(),
                'details' => $order->getDetails(),
            ),

            '_links' => array(
                'get' => $getToken->getTargetUrl(),
                'capture' => $captureToken->getTargetUrl(),
                'authorize' => $authorizeToken->getTargetUrl(),
                'notify' => $notifyToken->getTargetUrl(),
            )
        );
    }
}
