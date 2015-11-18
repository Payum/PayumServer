<?php
namespace Payum\Server\Form\Type;

use Payum\Server\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChooseGatewayType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gatewayName', 'payum_gateways_choice', ['constraints' => [new NotBlank()]])
            ->add('choose', 'submit')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => false,
            'attr' => ['class' => 'payum-choose-gateway'],
        ]);

        $resolver->setRequired('action');
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'choose_gateway';
    }
}