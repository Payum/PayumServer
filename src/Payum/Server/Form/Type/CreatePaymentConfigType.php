<?php
namespace Payum\Server\Form\Type;

use Payum\Server\Factory\Payment\FactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CreatePaymentConfigType extends AbstractType
{
    /**
     * @var FactoryInterface[]
     */
    private $factories;

    /**
     * @param FactoryInterface[] $factories
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();
        foreach ($this->factories as $factory) {
            $choices[$factory->getName()] = $factory->getTitle();
        }

        $builder
            ->add('name', 'text', array(
                'label' => 'Name',
                'constraints' => array(
                    new NotBlank,
                    new Regex(['pattern' => '[\w\d -_]', 'message' => 'The name must match [\w\d -_] regexp.']
                )),
            ))
            ->add('factory', 'choice', array(
                'label' => 'Gateway',
                'choices' => $choices,
                'constraints' => array(
                    new NotBlank,
                    new Choice(array('choices' => array_keys($choices)))
                )
            ))
        ;

        if (isset($options['factory'])) {
            $builder->add('options', 'form');

            $factory = $this->factories[$options['factory']];
            $factory->configureOptionsFormBuilder($builder->get('options'));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
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
        return 'create_payment_config';
    }
}