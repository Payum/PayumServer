<?php
namespace Payum\Server\Factory\Payment;

use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\SoapClientFactory;
use Payum\Payex\PaymentFactory;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PayexFactory implements FactoryInterface
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
    }

    /**
     * {@inheritDoc}
     */
    public function createPayment(array $options)
    {
        return PaymentFactory::create(new OrderApi(new SoapClientFactory(), $options));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'payex';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Payex';
    }
}