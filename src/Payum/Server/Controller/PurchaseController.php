<?php
namespace Payum\Server\Controller;

use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\RedirectUrlInteractiveRequest;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Server\Request\SecuredCaptureRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class PurchaseController
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
    public function doAction(Request $request)
    {
        $token = $this->httpRequestVerifier->verify($request);

        try {
            $payment = $this->registry->getPayment($token->getPaymentName());
            $payment->execute(new SecuredCaptureRequest($token));
        } catch (RedirectUrlInteractiveRequest $e) {
            return new RedirectResponse($e->getUrl());
        }

        $this->httpRequestVerifier->invalidate($token);

        return new RedirectResponse($token->getAfterUrl());
    }
}
