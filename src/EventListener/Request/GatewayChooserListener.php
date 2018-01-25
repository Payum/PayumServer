<?php
declare(strict_types=1);

namespace App\EventListener\Request;

use Payum\Core\Payum;
use Payum\Core\Reply\HttpResponse;
use App\Controller\GatewayChooserInterface;
use App\Form\Type\ChooseGatewayType;
use App\Model\Payment;
use App\Model\SecurityToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Class GatewayChooserListener
 * @package App\EventListener\Request
 */
class GatewayChooserListener implements EventSubscriberInterface
{
    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(
        Payum $payum,
        RequestStack $requestStack,
        FormFactoryInterface $formFactory,
        Environment $twig
    ) {
        $this->payum = $payum;
        $this->request = $requestStack->getCurrentRequest();
        $this->formFactory = $formFactory;
        $this->twig = $twig;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents() : array
    {
        return [
            KernelEvents::CONTROLLER => 'chooseGateway',
        ];
    }

    /**
     * Replace Request Token hash to Token Object
     *
     * @param FilterControllerEvent $event
     *
     * @throws \Exception
     */
    public function chooseGateway(FilterControllerEvent $event) : void
    {
        $controller = isset($event->getController()[0]) ? $event->getController()[0] : null;

        if (!($controller instanceof GatewayChooserInterface)) {
            return;
        }

        /** @var SecurityToken $token */
        $token = $this->payum->getHttpRequestVerifier()->verify($this->request);

        /** @var Payment $payment */
        $payment = $this->payum->getStorage(Payment::class)->find($token->getDetails()->getId());

        if (false == $payment->getGatewayName()) {
            $form = $this->formFactory->createNamed('', ChooseGatewayType::class, $payment, [
                'action' => $token->getTargetUrl(),
            ]);

            $form->handleRequest($this->request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->payum->getStorage($payment)->update($payment);
            } else {
                // the twig paths have to be initialized.
                $this->payum->getGatewayFactory('core')->create();

                throw new HttpResponse($this->twig->render('@PayumServer/chooseGateway.html.twig', [
                    'form' => $form->createView(),
                    'payment' => $payment,
                    'layout' => '@PayumCore/layout.html.twig',
                ]));
            }
        }

        $token->setGatewayName($payment->getGatewayName());

        // do not verify it second time.
        $this->request->attributes->set('payum_token', $token);
    }
}
