<?php
namespace Payum\Server\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayChoiceType;
use Payum\Server\Model\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Url;

class CreatePaymentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gatewayName', GatewayChoiceType::class, [
                'required' => false,
            ])
            ->add('totalAmount', NumberType::class, array(
                'label' => 'Amount',
                'constraints' => array(new NotBlank(), new Type(['type' => 'numeric']))
            ))
            ->add('currencyCode', ChoiceType::class, array(
                'choices' => ['US Dollar' => 'USD', 'Euro' => 'EUR', 'Swedish krona' => 'SEK'],
                'label' => 'Currency',
                'data' => 'USD',
                'constraints' => array(new NotBlank(), new Choice(['USD', 'EUR', 'SEK'])),
            ))
            ->add('clientEmail', TextType::class, array(
                'required' => false,
                'constraints' => array(new Email()))
            )
            ->add('clientId', TextType::class, array(
                'required' => false,
            ))
            ->add('description', TextType::class, array(
                'required' => false,
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'factory' => null,
        ]);
    }
}