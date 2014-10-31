<?php
namespace Payum\Server\Factory\Payment;

use Buzz\Client\Curl;
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
            ->add('accountNumber', 'text', array(
                'label' => 'Account Number',
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
            ->add('accountNumber', 'password', array(
                'label' => 'Encryption Key',
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

        'username' => 'REPLACE IT',
        'password' => 'REPLACE IT',
        'partner' => 'REPLACE IT',
        'vendor' => 'REPLACE IT',
        'sandbox' => true
    }

    /**
     * {@inheritDoc}
     */
    public function createPayment(array $options)
    {
        return PaymentFactory::create(new Api(new Curl(), $options));
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