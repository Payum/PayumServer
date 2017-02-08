<?php
namespace Payum\Server\Model;

use Makasim\Yadm\CastTrait;
use Makasim\Yadm\ValuesTrait;
use Payum\Core\Model\GatewayConfigInterface;

class GatewayConfig implements GatewayConfigInterface
{
    use ValuesTrait;
    use CastTrait;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getValue('id');
    }

    /**
     * {@inheritdoc}
     */
    public function getGatewayName()
    {
        return $this->getValue('gatewayName');
    }

    /**
     * {@inheritdoc}
     */
    public function setGatewayName($gatewayName)
    {
        $this->values['gatewayName'] = $gatewayName;
        $this->setValue('gatewayName', $gatewayName);
        $this->setValue('id', $gatewayName);
    }

    /**
     * {@inheritdoc}
     */
    public function getFactoryName()
    {
        return $this->getValue('factoryName');
    }

    /**
     * {@inheritdoc}
     */
    public function setFactoryName($name)
    {
        $this->setValue('factoryName', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config)
    {
        $this->setValue('config', $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->getValue('config', [], 'array');
    }
}
