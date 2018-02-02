<?php
declare(strict_types=1);

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

    public function __construct(Payment $payment, ?TokenInterface $token)
    {
        $this->payment = $payment;
        $this->token = $token;
    }

    public function getPayment() : Payment
    {
        return $this->payment;
    }

    public function getToken() : TokenInterface
    {
        return $this->token;
    }
}
