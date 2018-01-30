<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Storage\StorageInterface;
use App\Model\Payment;
use App\Storage\YadmStorage;
use App\Test\WebTestCase;

/**
 * Class ApplicationTest
 * @package App\Tests\Functional
 */
class ApplicationTest extends WebTestCase
{
    public function testShouldAllowGetPayumBuilderService()
    {
        $payum = $this->getContainer()->get('payum.builder');

        $this->assertInstanceOf(PayumBuilder::class, $payum);
    }

    public function testShouldAllowGetPayumService()
    {
        $payum = $this->getContainer()->get('payum');

        $this->assertInstanceOf(Payum::class, $payum);
    }

    public function testShouldAllowGetGatewayConfigStorageAsService()
    {
        $storage = $this->getContainer()->get('payum.yadm_gateway_config_storage');

        $this->assertInstanceOf(StorageInterface::class, $storage);
        $this->assertInstanceOf(YadmStorage::class, $storage);
    }

    public function testShouldAllowGetTokenStorageFromPayumService()
    {
        /** @var Payum $payum */
        $payum = $this->getContainer()->get('payum');

        $this->assertInstanceOf(YadmStorage::class, $payum->getTokenStorage());
    }

    public function testShouldAllowGetPaymentStorageFromPayumService()
    {
        /** @var Payum $payum */
        $payum = $this->getContainer()->get('payum');

        $this->assertInstanceOf(YadmStorage::class, $payum->getStorage(Payment::class));
    }

    public function testShouldAllowGetTokenFactoryFromPayumService()
    {
        /** @var Payum $payum */
        $payum = $this->getContainer()->get('payum');

        $this->assertInstanceOf(GenericTokenFactory::class, $payum->getTokenFactory());
    }
}