<?php
namespace Payum\Server\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\OrderToJsonConverter;
use Payum\Server\Model\Order;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @var OrderToJsonConverter
     */
    private $orderToJsonConverter;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FormToJsonConverter
     */
    private $formToJsonConverter;

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     * @param RegistryInterface $registry
     * @param OrderToJsonConverter $orderToJsonConverter
     * @param FormFactoryInterface $formFactory
     * @param FormToJsonConverter $formToJsonConverter
     */
    public function __construct(
        GenericTokenFactoryInterface $tokenFactory,
        HttpRequestVerifierInterface $httpRequestVerifier,
        RegistryInterface $registry,
        OrderToJsonConverter $orderToJsonConverter,
        FormFactoryInterface $formFactory,
        FormToJsonConverter $formToJsonConverter
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->registry = $registry;
        $this->formFactory = $formFactory;
        $this->formToJsonConverter = $formToJsonConverter;
        $this->httpRequestVerifier = $httpRequestVerifier;
        $this->orderToJsonConverter = $orderToJsonConverter;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction($content, Request $request)
    {
        $rawOrder = ArrayObject::ensureArrayObject($content);

        $form = $this->formFactory->create('create_order');
        $form->submit((array) $rawOrder);

        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }

        /** @var Order $order */
        $order = $form->getData();
        $order->setAfterUrl($order->getAfterUrl() ?: $request->getSchemeAndHttpHost());
        $order->setNumber($order->getNumber() ?: date('Ymd-'.mt_rand(10000, 99999)));
        $order->setDetails([]);

        $storage = $this->registry->getStorage($order);
        $storage->updateModel($order);

        $getToken = $this->tokenFactory->createToken($order->getPaymentName(), $order, 'order_get');

        $order->addToken('get', $getToken);
        $order->addToken('authorize', $this->tokenFactory->createAuthorizeToken($order->getPaymentName(), $order, $order->getAfterUrl()));
        $order->addToken('capture', $this->tokenFactory->createCaptureToken($order->getPaymentName(), $order, $order->getAfterUrl()));
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
    public function updateAction($content, Request $request)
    {
        $order = $this->findRequestedOrder($request);

        $rawOrder = ArrayObject::ensureArrayObject($content);

        $form = $this->formFactory->create('update_order', $order);
        $form->submit((array) $rawOrder);

        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }

        /** @var Order $order */
        $order = $form->getData();
        $order->setAfterUrl($order->getAfterUrl() ?: $request->getSchemeAndHttpHost());
        $order->setDetails([]);

        $storage = $this->registry->getStorage($order);
        $storage->updateModel($order);

        $getToken = $this->tokenFactory->createToken($order->getPaymentName(), $order, 'order_get');

        $order->addToken('get', $getToken);
        $order->addToken('authorize', $this->tokenFactory->createAuthorizeToken($order->getPaymentName(), $order, $order->getAfterUrl()));
        $order->addToken('capture', $this->tokenFactory->createCaptureToken($order->getPaymentName(), $order, $order->getAfterUrl()));
        $order->addToken('notify', $this->tokenFactory->createNotifyToken($order->getPaymentName(), $order));

        $storage->updateModel($order);

        return new JsonResponse(array(
            'order' => $this->orderToJsonConverter->convert($order),
            '_links' => $order->getLinks(),
        ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $order = $this->findRequestedOrder($request);

        return new JsonResponse(array(
            'order' => $this->orderToJsonConverter->convert($order),
            '_links' => $order->getLinks(),
        ));
    }

    /**
     * @return JsonResponse
     */
    public function metaAction()
    {
        $form = $this->formFactory->create('create_order');

        return new JsonResponse(array(
            'meta' => $this->formToJsonConverter->convertMeta($form),
        ));
    }

    /**
     * @param Request $request
     *
     * @return Order
     */
    protected function findRequestedOrder(Request $request)
    {
        $token = $this->httpRequestVerifier->verify($request);

        $storage = $this->registry->getStorage('Payum\Server\Model\Order');

        return $storage->findModelByIdentificator($token->getDetails());
    }
}
