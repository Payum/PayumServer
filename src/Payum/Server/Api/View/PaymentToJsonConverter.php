<?php
namespace Payum\Server\Api\View;

use Payum\Core\Registry\GatewayRegistryInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Server\Model\Payment;

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

    /**
     * @param Payment $payment
     *
     * @return array
     */
    public function convert(Payment $payment)
    {
        $normalizedPayment = [
            'id' => $payment->getId(),
            'status' => $payment->getStatus(),
            'gatewayName' => $payment->getGatewayName(),
            'number' => $payment->getNumber(),
            'totalAmount' => $payment->getTotalAmount(),
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
