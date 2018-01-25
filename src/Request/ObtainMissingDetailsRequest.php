<?php
namespace App\Request;

use Payum\Core\Security\TokenInterface;
use App\Model\Payment;

class ObtainMissingDetailsRequest
{
    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @param Payment $payment
     * @param TokenInterface $token
     */
    public function __construct(Payment $payment, TokenInterface $token = null)
    {
        $this->payment = $payment;
        $this->token = $token;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }
}