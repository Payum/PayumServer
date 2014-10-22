<?php
namespace Payum\Server\Form\Type;

use Payum\Server\Factory\Storage\FactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class CreateOrderType extends AbstractType
{
    /**
     * @var array
     */
    private $currentConfig;

    /**
     * @param array $currentConfig
     */
    public function __construct(array $currentConfig)
    {
        $this->currentConfig = $currentConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paymentName', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Choice(array('choices' => array_keys($this->currentConfig['payments'])))
                )
            ))
            ->add('afterUrl', 'text')
            ->add('totalAmount', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                )
            ))
            ->add('currencyCode', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                )
            ))
            ->add('clientEmail', 'text')
            ->add('clientId', 'text')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Payum\Server\Model\Order',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ));
        $resolver->setOptional(array('factory'));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'create_order';
    }
}