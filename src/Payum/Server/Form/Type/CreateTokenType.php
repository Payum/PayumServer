<?php
namespace Payum\Server\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ->add('type', ChoiceType::class, [
                'required' => true,
//                'empty_value' => false,
                'choices' => [
                    'Capture' => 'capture',
                    'Authorize' => 'authorize',
                ],
                'constraints' => array(new NotBlank())
            ])
            ->add('paymentId', TextType::class, array(
                'constraints' => array(new NotBlank())
            ))
            ->add('afterUrl', TextType::class, array(
                'constraints' => array(new NotBlank(), new Url()),
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ));
    }
}