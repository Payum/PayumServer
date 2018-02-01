<?php
declare(strict_types=1);

namespace App\Api\View;

use Payum\Core\Registry\GatewayRegistryInterface;
use App\Model\Payment;

class PaymentToJsonConverter
{
    /**
     * @var GatewayRegistryInterface
     */
    private $registry;

    /**
     * @param GatewayRegistryInterface $registry
     */
    public function __construct(GatewayRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function convert(Payment $payment) : array
    {
        $normalizedPayment = [
            'id' => $payment->getId(),
            'status' => $payment->getStatus(),
            'gatewayName' => $payment->getGatewayName(),
            'number' => $payment->getNumber(),
            'totalAmount' => $payment->getTotalAmount(),
            'totalAmountInput' => $payment->getValue('totalAmountInput'),
            'currencyCode' => $payment->getCurrencyCode(),
            'clientEmail' => $payment->getClientEmail(),
            'clientId' => $payment->getClientId(),
            'description' => $payment->getDescription(),
            'details' => $payment->getDetails(),
            '_links' => [],
        ];

        foreach (['self', 'done', 'capture', 'authorize', 'notify'] as $name) {
            if ($link = $payment->getValue('links', $name)) {
                $normalizedPayment['_links'][$name] = ['href' => $link];
            }
        }

        return $normalizedPayment;
    }
}
