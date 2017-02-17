<?php
namespace Payum\Server\Tests\Functional;

use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Model\Payment;
use Payum\Server\Storage\YadmStorage;
use Payum\Server\Test\WebTestCase;

class ApplicationTest extends WebTestCase
{
    public function testShouldAllowGetPayumBuilderService()
    {
        $payum = $this->app['payum.builder'];

        $this->assertInstanceOf(PayumBuilder::class, $payum);
    }

    public function testShouldAllowGetPayumService()
    {
        $payum = $this->app['payum'];

        $this->assertInstanceOf(Payum::class, $payum);
    }

    public function testShouldAllowGetGatewayConfigStorageAsService()
    {
        $storage = $this->app['payum.yadm_gateway_config_storage'];

        $this->assertInstanceOf(StorageInterface::class, $storage);
        $this->assertInstanceOf(YadmStorage::class, $storage);
    }

    public function testShouldAllowGetTokenStorageFromPayumService()
    {
        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $this->assertInstanceOf(YadmStorage::class, $payum->getTokenStorage());
    }

    public function testShouldAllowGetPaymentStorageFromPayumService()
    {
        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $this->assertInstanceOf(YadmStorage::class, $payum->getStorage(Payment::class));
    }

    public function testShouldAllowGetTokenFactoryFromPayumService()
    {
        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $this->assertInstanceOf(GenericTokenFactory::class, $payum->getTokenFactory());
    }
}