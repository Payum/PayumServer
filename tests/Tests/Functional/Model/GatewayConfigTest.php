<?php
declare(strict_types=1);

namespace App\Tests\Functional\Model;

use Makasim\Yadm\Storage;
use App\Model\GatewayConfig;
use App\Test\WebTestCase;

/**
 * Class GatewayConfigTest
 * @package App\Tests\Functional\Model
 */
class GatewayConfigTest extends WebTestCase
{
    public function testShouldAllowPersistGatewayConfigToMongo()
    {
        /** @var Storage $storage */
        $storage = $this->getGatewayConfigStorage();

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $storage->create();

        //guard
        $this->assertInstanceOf(GatewayConfig::class, $gatewayConfig);
        $this->assertNull($gatewayConfig->getId());

        $gatewayConfig->setGatewayName('theGatewayName');
        $gatewayConfig->setFactoryName('theFactoryName');
        $gatewayConfig->setConfig(['foo' => 'fooVal', 'bar' => 'barVal']);
        $storage->insert($gatewayConfig);

        $this->assertNotNull($gatewayConfig->getId());

        /** @var GatewayConfig $foundGatewayConfig */
        $foundGatewayConfig = $storage->findOne(['id' => $gatewayConfig->getId()]);

        $this->assertInstanceOf(GatewayConfig::class, $foundGatewayConfig);
        $this->assertNotSame($gatewayConfig, $foundGatewayConfig);
        $this->assertEquals($gatewayConfig->getId(), $foundGatewayConfig->getId());

        $this->assertEquals('theGatewayName', $foundGatewayConfig->getGatewayName());
        $this->assertEquals('theFactoryName', $foundGatewayConfig->getFactoryName());
        $this->assertEquals(['foo' => 'fooVal', 'bar' => 'barVal'], $foundGatewayConfig->getConfig());
    }
}