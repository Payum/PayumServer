<?php
namespace Payum\Server\Factory\Payment;

use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\AuthorizeNet\Aim\PaymentFactory;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuthorizeNetFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function configureOptionsFormBuilder(FormBuilderInterface $builder)
    {
        $builder
            ->add('login_id', 'text', array(
                'label' => 'Login Id',
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
            ->add('transaction_key', 'password', array(
                'label' => 'Transaction Key',
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
        return PaymentFactory::create(new AuthorizeNetAIM(
            $options['login_id'],
            $options['transaction_key']
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'authorize_net';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Authorize.Net';
    }
}