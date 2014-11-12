<?php
namespace Payum\Server\Factory\Payment;

use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Paypal\ProCheckout\Nvp\PaymentFactory;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaypalProCheckoutFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function configureOptionsFormBuilder(FormBuilderInterface $builder)
    {
        $builder
            ->add('username', 'text', array(
                'label' => 'Username',
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
            ->add('partner', 'text', array(
                'label' => 'Partner',
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
            ->add('vendor', 'text', array(
                'label' => 'Vendor',
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
            ->add('tender', 'text', array(
                'label' => 'Tender',
                'required' => true,
                'constraints' => array(new NotBlank),
                'data' => 'C',
            ))
            ->add('password', 'password', array(
                'label' => 'Password',
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
            ->add('sandbox', 'checkbox', array(
                'label' => 'Sandbox',
                'required' => false,
                'data' => true,
                'empty_data' => true,
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function createPayment(array $options)
    {
        return PaymentFactory::create(new Api($options));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'paypal_pro_checkout';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Paypal ProCheckout';
    }
}