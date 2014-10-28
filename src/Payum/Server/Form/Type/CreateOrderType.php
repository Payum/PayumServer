<?php
namespace Payum\Server\Form\Type;

use Payum\Server\Factory\Storage\FactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Url;

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
        $choices = [];
        foreach ($this->currentConfig['payments'] as $name => $data) {
            $choices[$name] = $name;
        }

        $builder
            ->add('paymentName', 'choice', array(
                'label' => 'Payment',
                'choices' => $choices,
                'constraints' => array(
                    new NotBlank(),
                    new Choice(array('choices' => array_keys($choices)))
                )
            ))
            ->add('afterUrl', 'text', array(
                'label' => 'After Url',
                'constraints' => array(new NotBlank(), new Url())
            ))
            ->add('totalAmount', 'number', array(
                'label' => 'Amount',
                'constraints' => array(new NotBlank(), new Type(['type' => 'numeric']))
            ))
            ->add('currencyCode', 'currency', array(
                'label' => 'Currency',
                'data' => 'USD',
                'constraints' => array(new NotBlank()),
                'preferred_choices' => array('USD', 'EUR'),
            ))
            ->add('clientEmail', 'text', array(
                'required' => false,
                'constraints' => array(new Email()))
            )
            ->add('clientId', 'text', array(
                'required' => false,
            ))
            ->add('description', 'text', array(
                'required' => false,
            ))
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