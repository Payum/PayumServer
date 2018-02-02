<?php
declare(strict_types=1);

namespace App\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Payum\Core\Bridge\Symfony\Form\Type\CreditCardType as BaseCreditCardType;

class CreditCardExtension implements FormTypeExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return BaseCreditCardType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('expireAt');

        $builder->add('expireAt', 'payum_credit_card_expiration_date', [
            'widget' => 'single_text',
            'input' => 'datetime',
            'label' => 'form.credit_card.expire_at',
            'html5' => false,
            'format' => 'MM/yy/dd',
            'attr' => ['placeholder' => 'mm/yy'],
        ]);

        $builder->get('expireAt')->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $event->setData($event->getData() . '/01');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
    }
}
