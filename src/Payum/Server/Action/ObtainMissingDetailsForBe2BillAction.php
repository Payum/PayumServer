<?php
namespace Payum\Server\Action;

use Payum\Core\Security\TokenInterface;
use Payum\Server\Model\Payer;
use Payum\Server\Model\Payment;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ObtainMissingDetailsForBe2BillAction extends ObtainMissingDetailsAction
{
    /**
     * {@inheritdoc}
     */
    protected function createPaymentFormBuilder(Payment $payment, TokenInterface $token = null)
    {
        $paymentFormBuilder = parent::createPaymentFormBuilder($payment, $token);
        $payerFormBuilder = $paymentFormBuilder->create('payer', 'form', [
            'data_class' => Payer::class,
            'csrf_protection' => false,
        ]);

        if (false == $payment->getPayer()->getEmail()) {
            $payerFormBuilder->add('email', 'text', ['constraints' => [new NotBlank(), new Email()]]);
        }

        if (count($payerFormBuilder) > 0) {
            $payerFormBuilder->add('submit', 'submit');

            $paymentFormBuilder->add($payerFormBuilder);
        }

        return $paymentFormBuilder;
    }
}