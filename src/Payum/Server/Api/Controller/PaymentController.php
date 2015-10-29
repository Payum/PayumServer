<?php
namespace Payum\Server\Api\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\Util\Random;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\PaymentToJsonConverter;
use Payum\Server\Model\Payment;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    private $paymentToJsonConverter;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var FormToJsonConverter
     */
    private $formToJsonConverter;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     * @param RegistryInterface $registry
     * @param PaymentToJsonConverter $paymentToJsonConverter
     * @param FormFactoryInterface $formFactory
     * @param FormToJsonConverter $formToJsonConverter
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        GenericTokenFactoryInterface $tokenFactory,
        HttpRequestVerifierInterface $httpRequestVerifier,
        RegistryInterface $registry,
        PaymentToJsonConverter $paymentToJsonConverter,
        FormFactoryInterface $formFactory,
        FormToJsonConverter $formToJsonConverter,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->registry = $registry;
        $this->formFactory = $formFactory;
        $this->formToJsonConverter = $formToJsonConverter;
        $this->httpRequestVerifier = $httpRequestVerifier;
        $this->paymentToJsonConverter = $paymentToJsonConverter;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction($content, Request $request)
    {
        $rawPayment = ArrayObject::ensureArrayObject($content);

        $form = $this->formFactory->create('create_payment');
        $form->submit((array) $rawPayment);
        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }

        /** @var Payment $payment */
        $payment = $form->getData();
        $payment->setPublicId(Random::generateToken());
        $payment->setNumber($payment->getNumber() ?: date('Ymd-'.mt_rand(10000, 99999)));

        $gateway = $this->registry->getGateway($payment->getGatewayName());
        $gateway->execute($convert = new Convert($payment, 'array'));
        $payment->setDetails($convert->getResult());

        $payment->addLink('self', $this->urlGenerator->generate('gateway_get',
            array('id' => $payment->getPublicId()),
            $absolute = true
        ));

        $storage = $this->registry->getStorage($payment);
        $storage->update($payment);


        $token = $this->tokenFactory->createAuthorizeToken($payment->getGatewayName(), $payment, $payment->getLink('done'), [
            'payum_token' => null,
        ]);
        $payment->addLink('authorize', $token->getTargetUrl(), [
            'payum_token' => null,
        ]);
        $token = $this->tokenFactory->createCaptureToken($payment->getGatewayName(), $payment, $payment->getLink('done'), [
            'payum_token' => null,
        ]);
        $payment->addLink('capture', $token->getTargetUrl());

        $token = $this->tokenFactory->createNotifyToken($payment->getGatewayName(), $payment);
        $payment->addLink('notify', $token->getTargetUrl());

        $storage->update($payment);

        return new JsonResponse(
            array(
                'payment' => $this->paymentToJsonConverter->convert($payment),
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

        $rawPayment = ArrayObject::ensureArrayObject($content);

        $form = $this->formFactory->create('update_payment', $payment);
        $form->submit((array) $rawPayment);

        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }

        /** @var Payment $payment */
        $payment = $form->getData();

        $gateway = $this->registry->getGateway($payment->getGatewayName());
        $gateway->execute($convert = new Convert($payment, 'array'));
        $payment->setDetails($convert->getResult());

        $storage = $this->registry->getStorage($payment);
        $storage->update($payment);

        return new JsonResponse(array(
            'payment' => $this->paymentToJsonConverter->convert($payment),
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
            'payment' => $this->paymentToJsonConverter->convert($payment),
        ));
    }

    /**
     * @return JsonResponse
     */
    public function allAction()
    {
        /** @var StorageInterface $storage */
        $storage = $this->registry->getStorage(Payment::class);

        $jsonPayments = [];
        foreach ($storage->findBy([]) as $payment) {
            $jsonPayments[] = $this->paymentToJsonConverter->convert($payment);

        }

        return new JsonResponse(array(
            'payments' => $jsonPayments,
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
        // TODO: add validation that id is not empty and model actually exists.
        $storage = $this->registry->getStorage(Payment::class);

        $payments = $storage->findBy([
            'publicId' => $request->attributes->get('id')
        ]);

        return array_shift($payments);
    }
}
