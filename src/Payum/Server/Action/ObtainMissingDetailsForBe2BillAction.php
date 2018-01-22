<?php
declare(strict_types=1);

namespace Payum\Server\Action;

use Payum\Core\Security\TokenInterface;
use Payum\Server\Model\Payer;
use Payum\Server\Model\Payment;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ObtainMissingDetailsForBe2BillAction extends ObtainMissingDetailsAction
{
    /**
     * {@inheritdoc}
     */
    protected function createPaymentFormBuilder(Payment $payment, TokenInterface $token = null) : FormBuilderInterface
    {
        $paymentFormBuilder = parent::createPaymentFormBuilder($payment, $token);
        $payerFormBuilder = $paymentFormBuilder->create('payer', FormType::class, [
            'data_class' => Payer::class,
            'csrf_protection' => false,
        ]);

        if (false == $payment->getPayer()->getEmail()) {
            $payerFormBuilder->add('email', TextType::class, ['constraints' => [new NotBlank(), new Email()]]);
        }

        if (count($payerFormBuilder) > 0) {
            $payerFormBuilder->add('submit', SubmitType::class);

            $paymentFormBuilder->add($payerFormBuilder);
        }

        return $paymentFormBuilder;
    }
}