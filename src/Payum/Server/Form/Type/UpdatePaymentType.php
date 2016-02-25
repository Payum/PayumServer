<?php
namespace Payum\Server\Form\Type;

use Payum\Server\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdatePaymentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'method' => 'PATCH',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return CreatePaymentType::class;
    }
}