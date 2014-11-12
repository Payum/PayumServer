<?php
namespace Payum\Server\Factory\Payment;

use Payum\Be2Bill\Api;
use Payum\Be2Bill\OnsitePaymentFactory;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class Be2BillOffsiteFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function configureOptionsFormBuilder(FormBuilderInterface $builder)
    {
        $builder
            ->add('identifier', 'text', array(
                'label' => 'Identifier',
                'required' => true,
                'constraints' => array(new NotBlank),
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
        return OnsitePaymentFactory::create(new Api($options));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'be2bill_offsite';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Be2Bill Offsite';
    }
}