<?php
namespace Payum\Server\Factory\Payment;

use Payum\Core\PaymentInterface;
use Symfony\Component\Form\FormBuilderInterface;

interface FactoryInterface
{
    /**
     * @param FormBuilderInterface $builder
     */
    function configureOptionsFormBuilder(FormBuilderInterface $builder);

    /**
     * @param array $options
     *
     * @return PaymentInterface
     */
    function createPayment(array $options);

    /**
     * @return string
     */
    function getName();

    /**
     * @return string
     */
    function getTitle();
}
