<?php
namespace Payum\Server\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Server\Model\Order;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function createAction($content, Request $request)
    {
        $rawOrder = ArrayObject::ensureArrayObject($content);

        $storage = $this->registry->getStorage('Payum\Server\Model\Order');

        $afterUrl = $rawOrder['afterUrl'] ?: $request->getSchemeAndHttpHost();

        /** @var Order $order */
        $order = $storage->createModel();
        $order->setPaymentName($rawOrder['paymentName']);
        $order->setNumber(date('Ymd-'.mt_rand(10000, 99999)));
        $order->setTotalAmount($rawOrder['totalAmount']);
        $order->setCurrencyCode($rawOrder['currencyCode']);
        $order->setClientEmail($rawOrder['clientEmail']);
        $order->setClientId($rawOrder['clientId']);
        $order->setDetails(is_array($rawOrder['details']) ? $rawOrder['details'] : array());

        $getToken = $this->tokenFactory->createToken($order->getPaymentName(), $order, 'order_get');

        $order->addToken('get', $getToken);
        $order->addToken('authorize', $this->tokenFactory->createAuthorizeToken($order->getPaymentName(), $order, $afterUrl));
        $order->addToken('capture', $this->tokenFactory->createCaptureToken($order->getPaymentName(), $order, $afterUrl));
        $order->addToken('notify', $this->tokenFactory->createNotifyToken($order->getPaymentName(), $order));

        $storage->updateModel($order);

        return new Response('', 201, array(
            'Location' => $getToken->getTargetUrl()
        ));
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

        $orderPayments = array();
        foreach ($order->getPayments() as $orderPayment) {
            if (false == isset($orderPayment['status']) || GetHumanStatus::STATUS_UNKNOWN == $orderPayment['status']) {
                $payment->execute($status = new GetHumanStatus($orderPayment['details']));
                $orderPayment['status'] = $status->getValue();

            }

            $orderPayments[] = $orderPayment;
        }

        $order->setPayments($orderPayments);

        $payment->execute($status = new GetHumanStatus($order));

        return new JsonResponse($this->buildJsonOrder($order));
    }

    public function buildJsonOrder(Order $order)
    {
        return array(
            'order' => array(
                'number' => $order->getNumber(),
                'totalAmount' => $order->getTotalAmount(),
                'currencyCode' => $order->getCurrencyCode(),
                'clientEmail' => $order->getClientEmail(),
                'clientId' => $order->getClientId(),
                'payments' => $order->getPayments(),
            ),

            '_tokens' => $order->getTokens(),
            '_links' => $order->getLinks(),
        );
    }
}
