<?php
namespace Payum\Server\Api\Controller;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payum;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Security\Util\Random;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Api\View\FormToJsonConverter;
use Payum\Server\Api\View\PaymentToJsonConverter;
use Payum\Server\Controller\ForwardExtensionTrait;
use Payum\Server\Model\Payment;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController
{
    use ForwardExtensionTrait;

    /**
     * @var RegistryInterface
     */
    protected $payum;

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
     * @param Payum $payum
     * @param PaymentToJsonConverter $paymentToJsonConverter
     * @param FormFactoryInterface $formFactory
     * @param FormToJsonConverter $formToJsonConverter
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        Payum $payum,
        PaymentToJsonConverter $paymentToJsonConverter,
        FormFactoryInterface $formFactory,
        FormToJsonConverter $formToJsonConverter,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->payum = $payum;
        $this->formFactory = $formFactory;
        $this->formToJsonConverter = $formToJsonConverter;
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
        $this->forward400Unless('json' == $request->getContentType() || 'form' == $request->getContentType());

        $rawPayment = ArrayObject::ensureArrayObject($content);

        $form = $this->formFactory->create('create_payment');
        $form->submit((array) $rawPayment);
        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }

        /** @var Payment $payment */
        $payment = $form->getData();
        $payment->setId(Random::generateToken());

        $storage = $this->payum->getStorage($payment);
        $storage->update($payment);

        $payment->setNumber($payment->getNumber() ?: date('Ymd-'.mt_rand(10000, 99999)));

        if ($payment->getGatewayName()) {
            $gateway = $this->payum->getGateway($payment->getGatewayName());
            $gateway->execute($convert = new Convert($payment, 'array'));
            $payment->setDetails($convert->getResult());
        }

        $storage->update($payment);

        // TODO
        $payment->setValue('links', 'done', 'http://dev.payum-server.com/client/index.html');

        $payment->setValue('links', 'self', $this->urlGenerator->generate('payment_get', ['id' => $payment->getId()], true));

        $token = $this->payum->getTokenFactory()->createAuthorizeToken($payment->getGatewayName(), $payment, $payment->getValue('links', 'done'), [
            'payum_token' => null,
            'payment' => $payment->getId(),
        ]);
        $payment->setValue('links', 'authorize', $token->getTargetUrl());

        $token = $this->payum->getTokenFactory()->createCaptureToken($payment->getGatewayName(), $payment, $payment->getValue('links', 'done'), [
            'payum_token' => null,
            'payment' => $payment->getId(),
        ]);
        $payment->setValue('links', 'capture', $token->getTargetUrl());

        $token = $this->payum->getTokenFactory()->createNotifyToken($payment->getGatewayName(), $payment);
        $payment->setValue('links', 'notify', $token->getTargetUrl());

        $storage->update($payment);

        return new JsonResponse(
            array(
                'payment' => $this->paymentToJsonConverter->convert($payment),
            ),
            201,
            array('Location' => $payment->getValue('links', 'self'))
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction($content, Request $request)
    {
        $this->forward400Unless('json' == $request->getContentType());

        $payment = $this->findRequestedPayment($request);

        $rawPayment = ArrayObject::ensureArrayObject($content);

        $form = $this->formFactory->create('update_payment', $payment);
        $form->submit((array) $rawPayment);

        if (false == $form->isValid()) {
            return new JsonResponse($this->formToJsonConverter->convertInvalid($form), 400);
        }

        /** @var Payment $payment */
        $payment = $form->getData();

        if ($payment->getGatewayName()) {
            $gateway = $this->payum->getGateway($payment->getGatewayName());
            $gateway->execute($convert = new Convert($payment, 'array'));
            $payment->setDetails($convert->getResult());
        }

        $storage = $this->payum->getStorage($payment);
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

        $storage = $this->payum->getStorage($payment);
        $storage->delete($payment);

        $token = $this->payum->getHttpRequestVerifier()->verify($request);
        $this->payum->getHttpRequestVerifier()->invalidate($token);

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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function allAction(Request $request)
    {
        /** @var StorageInterface $storage */
        $storage = $this->payum->getStorage(Payment::class);

        $jsonPayments = [];
        foreach ($storage->findBy([]) as $payment) {
            $jsonPayments[] = $this->paymentToJsonConverter->convert($payment);

        }

        return new JsonResponse(array(
            'payments' => $jsonPayments,
        ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function metaAction(Request $request)
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
        $storage = $this->payum->getStorage(Payment::class);

        return $storage->find($request->attributes->get('id'));
    }
}
