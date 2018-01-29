<?php
declare(strict_types=1);

namespace App\Form\Type;

use Payum\Core\Bridge\Symfony\Form\Type\GatewayChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ChooseGatewayType
 * @package App\Form\Type
 */
class ChooseGatewayType extends AbstractType
{
    /**
     * @var callable
     */
    private $gatewayChoices;

    /**
     * @param callable $gatewayChoices
     */
    public function __construct(callable $gatewayChoices)
    {
        $this->gatewayChoices = $gatewayChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gatewayName', GatewayChoiceType::class, [
                'constraints' => [new NotBlank()],
                'choice_loader' => new CallbackChoiceLoader($this->gatewayChoices),
            ])
            ->add('choose', SubmitType::class);
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => false,
            'attr' => ['class' => 'payum-choose-gateway'],
        ]);

        $resolver->setRequired('action');
    }
}
