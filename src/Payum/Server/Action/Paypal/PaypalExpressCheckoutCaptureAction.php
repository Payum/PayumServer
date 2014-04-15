<?php
namespace Payum\Server\Action\Paypal;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Server\Request\SecuredCaptureRequest;
use Payum\Core\Request\SecuredCaptureRequest as CoreSecuredCaptureRequest;
use Silex\Application;

class PaypalExpressCheckoutCaptureAction extends PaymentAwareAction
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var GenericTokenFactoryInterface $tokenFactory */
        $tokenFactory = $this->app['payum.security.token_factory'];
        $model = $request->getModel();

        if (false == isset($model['PAYMENTREQUEST_0_NOTIFYURL'])) {
            $notifyToken = $tokenFactory->createNotifyToken(
                $request->getToken()->getPaymentName(),
                $model
            );

            $model['PAYMENTREQUEST_0_NOTIFYURL'] = $notifyToken->getTargetUrl();
        }

        $coreRequest = new CoreSecuredCaptureRequest($request->getToken());
        $coreRequest->setModel($model);

        $this->payment->execute($coreRequest);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof SecuredCaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
