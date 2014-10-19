<?php
namespace Payum\Server\Factory\Storage;

use Payum\Core\PaymentInterface;
use Symfony\Component\Form\FormBuilderInterface;

interface FactoryInterface
{
    /**
     * @param FormBuilderInterface $builder
     */
    function configureOptionsFormBuilder(FormBuilderInterface $builder);

    /**
     * @param string $modelClass
     * @param string $idProperty
     * @param array $options
     *
     * @return PaymentInterface
     */
    function createStorage($modelClass, $idProperty, array $options);

    /**
     * @return string
     */
    function getName();
}
