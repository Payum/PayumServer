<?php
namespace App\Model;

use Makasim\Values\CastTrait;
use Makasim\Values\ValuesTrait;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;

class GatewayConfig implements GatewayConfigInterface, CryptedInterface
{
    use ValuesTrait;
    use CastTrait;

    private $decryptedConfig = [];

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
        $this->setValue('config.factory', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $config)
    {
        $this->setValue('config', $config);
        $this->decryptedConfig = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        if ($this->getValue('config.encrypted', false)) {
            return $this->decryptedConfig;
        }

        return $this->getValue('config', [], 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(CypherInterface $cypher)
    {
        if (false == $this->getValue('config.encrypted', false)) {
            return;
        }

        foreach ($this->getValue('config', []) as $name => $value) {
            if ('encrypted' == $name || is_bool($value)) {
                $this->decryptedConfig[$name] = $value;

                continue;
            }

            $this->decryptedConfig[$name] = $cypher->decrypt($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(CypherInterface $cypher)
    {
        if (false == $this->getValue('config.encrypted', false)) {
            $decryptedConfig = $this->getValue('config', []);
        } else {
            $decryptedConfig = $this->decryptedConfig;
        }

        $decryptedConfig['encrypted'] = true;

        $config = [];
        foreach ($decryptedConfig as $name => $value) {
            if ('encrypted' == $name || is_bool($value)) {
                $config[$name] = $value;

                continue;
            }

            $config[$name] = $cypher->encrypt($value);
        }

        $this->setValue('config', $config);
    }
}
