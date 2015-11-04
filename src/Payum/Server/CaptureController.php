<?php
namespace Payum\Server;

use Payum\Core\Payum;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Model\Payment;
use Payum\Silex\CaptureController as BaseCaptureController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

class CaptureController extends BaseCaptureController
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        parent::__construct($app['payum']);

        $this->app = $app;
    }

    /**
     * {@inheritDoc}
     */
    public function doAction(Request $request)
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        /** @var StorageInterface $paymentStorage */
        $paymentStorage = $this->payum->getStorage($token->getDetails()->getClass());

        /** @var Payment $payment */
        $payment = $paymentStorage->find($token->getDetails()->getId());

        if (false == $payment->getGatewayName()) {
            /** @var FormFactoryInterface $formFactory */
            $formFactory = $this->app['form.factory'];

            $form = $formFactory->createBuilder('form', $payment, [
                'method' => 'POST',
                'action' => $token->getTargetUrl(),
                'csrf_protection' => false,
            ])
                ->add('gatewayName', 'payum_gateways_choice', ['constraints' => [new NotBlank()]])
                ->add('choose', 'submit')

                ->getForm()
            ;

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $paymentStorage->update($payment);
            } else {
                /** @var \Twig_Environment $twig */
                $twig = $this->app['twig'];

                return new Response($twig->render('@PayumServer/chooseGateway.html.twig', [
                    'form' => $form->createView(),
                    'layout' => '@PayumCore/layout.html.twig',
                ]));
            }
        }

        $token->setGatewayName($payment->getGatewayName());
        $this->payum->getTokenStorage()->update($token);

        // do not verify it second time.
        $request->attributes->set('payum_token', $token);

        return parent::doAction($request);
    }

}