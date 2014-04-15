<?php
namespace Payum\Server\Controller;

use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\SecuredNotifyRequest;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotifyController
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var HttpRequestVerifierInterface
     */
    protected $httpRequestVerifier;

    /**
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     * @param RegistryInterface $registry
     */
    public function __construct(
        HttpRequestVerifierInterface $httpRequestVerifier,
        RegistryInterface $registry
    ) {
        $this->registry = $registry;
        $this->httpRequestVerifier = $httpRequestVerifier;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function doAction(Request $request)
    {
        $token = $this->httpRequestVerifier->verify($request);

        $payment = $this->registry->getPayment($token->getPaymentName());

        $payment->execute(new SecuredNotifyRequest(
            array_replace($request->query->all(), $request->request->all()),
            $token
        ));

        return new Response('', 204);
    }
}
