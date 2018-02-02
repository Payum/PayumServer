<?php
declare(strict_types=1);

namespace App\Api\View;

use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\Util\Mask;

class GatewayConfigToJsonConverter
{
    public function convert(GatewayConfigInterface $gatewayConfig) : array
    {
        $config = [];
        foreach ($gatewayConfig->getConfig() as $name => $value) {
            $config[$name] = Mask::mask($value, '*');
        }

        return [
            'gatewayName' => $gatewayConfig->getGatewayName(),
            'factoryName' => $gatewayConfig->getFactoryName(),
            'config' => $config,
        ];
    }
}
