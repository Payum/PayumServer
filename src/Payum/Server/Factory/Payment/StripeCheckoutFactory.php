<?php
namespace Payum\Server\Factory\Payment;

use Payum\Stripe\Keys;
use Payum\Stripe\PaymentFactory;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class StripeCheckoutFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function configureOptionsFormBuilder(FormBuilderInterface $builder)
    {
        $builder
            ->add('publishable_key', 'text', array(
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
            ->add('secret_key', 'password', array(
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function createPayment(array $options)
    {
        return PaymentFactory::createCheckout(new Keys($options['publishable_key'], $options['secret_key']));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'stripe_checkout';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Stripe Checkout';
    }
}