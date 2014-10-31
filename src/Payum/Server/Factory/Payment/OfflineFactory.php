<?php
namespace Payum\Server\Factory\Payment;

use Payum\Offline\PaymentFactory;
use Symfony\Component\Form\FormBuilderInterface;

class OfflineFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function configureOptionsFormBuilder(FormBuilderInterface $builder)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function createPayment(array $options)
    {
        return PaymentFactory::create();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'offline';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Offline';
    }
}