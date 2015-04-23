<?php
namespace Payum\Server\Api\View;

use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\Util\Mask;

class GatewayConfigToJsonConverter
{
    /**
     * @param GatewayConfigInterface $gatewayConfig
     *
     * @return array
     */
    public function convert(GatewayConfigInterface $gatewayConfig)
    {
        $config = array();
        foreach ($gatewayConfig->getConfig() as $name => $value) {
            $config[$name] = Mask::mask($value, '*');
        }

        return array(
            'gatewayName' => $gatewayConfig->getGatewayName(),
            'factoryName' => $gatewayConfig->getFactoryName(),
            'config' => $config,
        );
    }
}
