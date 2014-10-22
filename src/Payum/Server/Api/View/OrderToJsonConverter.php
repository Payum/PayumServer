<?php
namespace Payum\Server\Api\View;

use Payum\Server\Model\Order;

class OrderToJsonConverter
{
    /**
     * @param Order $order
     *
     * @return array
     */
    public function convert(Order $order)
    {
        return array(
            'number' => $order->getNumber(),
            'totalAmount' => $order->getTotalAmount(),
            'currencyCode' => $order->getCurrencyCode(),
            'clientEmail' => $order->getClientEmail(),
            'clientId' => $order->getClientId(),
            'payments' => $order->getPayments(),
        );
    }
}