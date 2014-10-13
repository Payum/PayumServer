<?php
namespace Payum\Server\Controller;

use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Refund;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PayumController
{
    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var HttpRequestVerifierInterface
     */
    protected $httpRequestVerifier;

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     * @param RegistryInterface $registry
     */
    public function __construct(
        GenericTokenFactoryInterface $tokenFactory,
        HttpRequestVerifierInterface $httpRequestVerifier,
        RegistryInterface $registry
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->registry = $registry;
        $this->httpRequestVerifier = $httpRequestVerifier;
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function authorizeAction(Request $request)
    {
        $token = $this->httpRequestVerifier->verify($request);

        $payment = $this->registry->getPayment($token->getPaymentName());
        $payment->execute(new Authorize($token));

        $this->httpRequestVerifier->invalidate($token);

        return new RedirectResponse($token->getAfterUrl());
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function captureAction(Request $request)
    {
        $token = $this->httpRequestVerifier->verify($request);

        $payment = $this->registry->getPayment($token->getPaymentName());
        $payment->execute(new Capture($token));

        $this->httpRequestVerifier->invalidate($token);

        return new RedirectResponse($token->getAfterUrl());
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function refundAction(Request $request)
    {
        $token = $this->httpRequestVerifier->verify($request);

        $payment = $this->registry->getPayment($token->getPaymentName());
        $payment->execute(new Refund($token));

        $this->httpRequestVerifier->invalidate($token);

        return new RedirectResponse($token->getAfterUrl());
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function notifyAction(Request $request)
    {
        $token = $this->httpRequestVerifier->verify($request);

        $payment = $this->registry->getPayment($token->getPaymentName());

        $payment->execute(new Notify($token));

        return new Response('', 204);
    }
}
