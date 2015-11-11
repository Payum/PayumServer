<?php
namespace Payum\Server\Tests\Functional\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Payum\Core\Payum;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Factory\Storage\FactoryInterface;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Storage\MongoStorage;
use Payum\Server\Test\WebTestCase;

class GatewayConfigTest extends WebTestCase
{
    public function testShouldAllowPersistGatewayConfigToMongo()
    {
        /** @var StorageInterface $storage */
        $storage = $this->app['payum.gateway_config_storage'];

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $storage->create();

        //guard
        $this->assertInstanceOf(GatewayConfig::class, $gatewayConfig);
        $this->assertNull($gatewayConfig->getId());

        $gatewayConfig->setGatewayName('theGatewayName');
        $gatewayConfig->setFactoryName('theFactoryName');
        $gatewayConfig->setConfig(['foo' => 'fooVal', 'bar' => 'barVal']);

        $storage->update($gatewayConfig);

        $this->assertNotNull($gatewayConfig->getId());

        /** @var GatewayConfig $foundGatewayConfig */
        $foundGatewayConfig = $storage->find($gatewayConfig->getId());

        $this->assertInstanceOf(GatewayConfig::class, $foundGatewayConfig);
        $this->assertNotSame($gatewayConfig, $foundGatewayConfig);
        $this->assertEquals($gatewayConfig->getId(), $foundGatewayConfig->getId());

        $this->assertEquals('theGatewayName', $foundGatewayConfig->getGatewayName());
        $this->assertEquals('theFactoryName', $foundGatewayConfig->getFactoryName());
        $this->assertEquals(['foo' => 'fooVal', 'bar' => 'barVal'], $foundGatewayConfig->getConfig());
    }
}