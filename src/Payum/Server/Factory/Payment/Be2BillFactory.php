<?php
namespace Payum\Server\Factory\Payment;

use Buzz\Client\Curl;
use Payum\Be2Bill\Api;
use Payum\Be2Bill\PaymentFactory;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class Be2BillFactory implements FactoryInterface
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
        return PaymentFactory::create(new Api(new Curl(), $options));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'be2bill';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Be2Bill';
    }
}