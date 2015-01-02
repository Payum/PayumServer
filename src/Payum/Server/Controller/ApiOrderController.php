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
use Payum\Server\Storage\StorageInterface;
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
        $storage->update($order);

        $token = $this->tokenFactory->createToken($order->getPaymentName(), $order, 'order_get');
        $order->setPublicId($token->getHash());
        $order->addLink('self', $token->getTargetUrl());
        $order->addLink('update', $token->getTargetUrl());
        $order->addLink('delete', $token->getTargetUrl());

        $token = $this->tokenFactory->createAuthorizeToken($order->getPaymentName(), $order, $order->getAfterUrl(), [
            'payum_token' => null
        ]);
        $order->addLink('authorize', $token->getTargetUrl());

        $token = $this->tokenFactory->createCaptureToken($order->getPaymentName(), $order, $order->getAfterUrl(), [
            'payum_token' => null
        ]);
        $order->addLink('capture', $token->getTargetUrl());

        $token = $this->tokenFactory->createNotifyToken($order->getPaymentName(), $order);
        $order->addLink('notify', $token->getTargetUrl());

        $storage->update($order);

        return new JsonResponse(
            array(
                'order' => $this->orderToJsonConverter->convert($order),
            ),
            201,
            array('Location' => $order->getLink('self'))
        );
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
        $storage->update($order);

        $token = $this->tokenFactory->createAuthorizeToken($order->getPaymentName(), $order, $order->getAfterUrl());
        $order->addLink('authorize', $token->getTargetUrl());

        $token = $this->tokenFactory->createCaptureToken($order->getPaymentName(), $order, $order->getAfterUrl());
        $order->addLink('capture', $token->getTargetUrl());

        $token = $this->tokenFactory->createNotifyToken($order->getPaymentName(), $order);
        $order->addLink('notify', $token->getTargetUrl());

        $storage->update($order);

        return new JsonResponse(array(
            'order' => $this->orderToJsonConverter->convert($order),
        ));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $order = $this->findRequestedOrder($request);

        $storage = $this->registry->getStorage($order);
        $storage->delete($order);

        $token = $this->httpRequestVerifier->verify($request);
        $this->httpRequestVerifier->invalidate($token);

        //TODO remove tokens.

        return new Response('', 204);
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
        ));
    }

    /**
     * @return JsonResponse
     */
    public function getAllAction()
    {
        /** @var StorageInterface $storage */
        $storage = $this->registry->getStorage(Order::class);

        $jsonOrders = [];
        foreach ($storage->findAll() as $order) {
            $jsonOrders[] = $this->orderToJsonConverter->convert($order);

        }

        return new JsonResponse(array(
            'orders' => $jsonOrders,
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
