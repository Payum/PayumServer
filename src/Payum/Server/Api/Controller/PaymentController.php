<?php
namespace Payum\Server\Api\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\PaymentToJsonConverter;
use Payum\Server\Model\Payment;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentController
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
     * @var PaymentToJsonConverter
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
     * @param PaymentToJsonConverter $orderToJsonConverter
     * @param FormFactoryInterface $formFactory
     * @param FormToJsonConverter $formToJsonConverter
     */
    public function __construct(
        GenericTokenFactoryInterface $tokenFactory,
        HttpRequestVerifierInterface $httpRequestVerifier,
        RegistryInterface $registry,
        PaymentToJsonConverter $orderToJsonConverter,
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

        $form = $this->formFactory->create('create_payment');
        $form->submit((array) $rawOrder);
        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }

        /** @var Payment $payment */
        $payment = $form->getData();
        $payment->setAfterUrl($payment->getAfterUrl() ?: $request->getSchemeAndHttpHost());
        $payment->setNumber($payment->getNumber() ?: date('Ymd-'.mt_rand(10000, 99999)));
        $payment->setDetails([]);

        $storage = $this->registry->getStorage($payment);
        $storage->update($payment);

        $token = $this->tokenFactory->createToken($payment->getGatewayName(), $payment, 'order_get');
        $payment->setPublicId($token->getHash());
        $payment->addLink('self', $token->getTargetUrl());
        $payment->addLink('update', $token->getTargetUrl());
        $payment->addLink('delete', $token->getTargetUrl());

        $token = $this->tokenFactory->createAuthorizeToken($payment->getGatewayName(), $payment, $payment->getAfterUrl(), [
            'payum_token' => null
        ]);
        $payment->addLink('authorize', $token->getTargetUrl());

        $token = $this->tokenFactory->createCaptureToken($payment->getGatewayName(), $payment, $payment->getAfterUrl(), [
            'payum_token' => null
        ]);
        $payment->addLink('capture', $token->getTargetUrl());

        $token = $this->tokenFactory->createNotifyToken($payment->getGatewayName(), $payment);
        $payment->addLink('notify', $token->getTargetUrl());

        $storage->update($payment);

        return new JsonResponse(
            array(
                'order' => $this->orderToJsonConverter->convert($payment),
            ),
            201,
            array('Location' => $payment->getLink('self'))
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction($content, Request $request)
    {
        $payment = $this->findRequestedPayment($request);

        $rawOrder = ArrayObject::ensureArrayObject($content);

        $form = $this->formFactory->create('update_payment', $payment);
        $form->submit((array) $rawOrder);

        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }

        /** @var Payment $payment */
        $payment = $form->getData();
        $payment->setAfterUrl($payment->getAfterUrl() ?: $request->getSchemeAndHttpHost());
        $payment->setDetails([]);

        $storage = $this->registry->getStorage($payment);
        $storage->update($payment);

        $token = $this->tokenFactory->createAuthorizeToken($payment->getGatewayName(), $payment, $payment->getAfterUrl());
        $payment->addLink('authorize', $token->getTargetUrl());

        $token = $this->tokenFactory->createCaptureToken($payment->getGatewayName(), $payment, $payment->getAfterUrl());
        $payment->addLink('capture', $token->getTargetUrl());

        $token = $this->tokenFactory->createNotifyToken($payment->getGatewayName(), $payment);
        $payment->addLink('notify', $token->getTargetUrl());

        $storage->update($payment);

        return new JsonResponse(array(
            'order' => $this->orderToJsonConverter->convert($payment),
        ));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $payment = $this->findRequestedPayment($request);

        $storage = $this->registry->getStorage($payment);
        $storage->delete($payment);

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
        $payment = $this->findRequestedPayment($request);

        return new JsonResponse(array(
            'order' => $this->orderToJsonConverter->convert($payment),
        ));
    }

    /**
     * @return JsonResponse
     */
    public function allAction()
    {
        /** @var StorageInterface $storage */
        $storage = $this->registry->getStorage(Payment::class);

        $jsonOrders = [];
        foreach ($storage->findBy([]) as $payment) {
            $jsonOrders[] = $this->orderToJsonConverter->convert($payment);

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
        $form = $this->formFactory->create('create_payment');

        return new JsonResponse(array(
            'meta' => $this->formToJsonConverter->convertMeta($form),
        ));
    }

    /**
     * @param Request $request
     *
     * @return Payment
     */
    protected function findRequestedPayment(Request $request)
    {
        $token = $this->httpRequestVerifier->verify($request);

        $storage = $this->registry->getStorage('Payum\Server\Model\Payment');

        return $storage->find($token->getDetails());
    }
}
