<?php
namespace Payum\Server\Tests\Functional\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Payum\Server\Factory\Storage\FactoryInterface;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Test\WebTestCase;

class GatewayConfigTest extends WebTestCase
{
    public function testShouldAllowPersistGatewayConfigToMongo()
    {
        /** @var FactoryInterface $factory */
        $factory = $this->app['payum.storage_factories']['doctrine_mongodb'];

        $storage = $factory->createStorage(GatewayConfig::class, 'id', [
            'host' => 'localhost:27017',
            'databaseName' => 'payum_server_tests',
        ]);

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

        /** @var DocumentManager $dm */
        $dm = $this->readAttribute($storage, 'objectManager');
        $dm->clear();

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