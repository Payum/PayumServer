<?php
namespace Payum\Server\Form\Type;

use Payum\Server\Model\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Url;

class CreateTokenType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', [
                'required' => true,
                'empty_value' => false,
                'choices' => [
                    'capture' => 'Capture',
                    'authorize' => 'Authorize',
                ],
                'constraints' => array(new NotBlank())
            ])
            ->add('paymentId', 'text', array(
                'constraints' => array(new NotBlank())
            ))
            ->add('afterUrl', 'text', array(
                'constraints' => array(new NotBlank(), new Url()),
            ))
        ;
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
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'create_token';
    }
}