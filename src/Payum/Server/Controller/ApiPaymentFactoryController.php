<?php
namespace Payum\Server\Controller;

use Payum\Server\Factory\Payment\PaypalExpressCheckoutFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiPaymentFactoryController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @return JsonResponse
     */
    public function getAllAction()
    {
        $builder = $this->formFactory->createBuilder('form', null, array(
            'csrf_protection' => false,
        ));

        $paypalPaymentFactory = new PaypalExpressCheckoutFactory();
        $paypalPaymentFactory->configureOptionsFormBuilder($builder);

        $formView = $builder->getForm()->createView();

        $options = array();
        foreach ($formView->children as $name => $child) {
            $options[$name] = array(
                'default' => $child->vars['data'],
                'label' => $child->vars['label'],
                'required' => $child->vars['required'],
            );

            if (in_array('text', $child->vars['block_prefixes'])) {
                $options[$name]['type'] = 'text';
            } elseif (in_array('checkbox', $child->vars['block_prefixes'])) {
                $options[$name]['type'] = 'checkbox';
            } else {
                $options[$name]['type'] = 'text';
            }
        }

        return new JsonResponse(array('factories' => array(
            $paypalPaymentFactory->getName() => $options
        )));
    }
}
