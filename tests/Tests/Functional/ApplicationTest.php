<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use Makasim\Yadm\Storage;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Security\GenericTokenFactory;
use App\Storage\GatewayConfigStorage;
use App\Model\Payment;
use App\Storage\YadmStorage;
use App\Test\WebTestCase;

class ApplicationTest extends WebTestCase
{
    public function testShouldAllowGetPayumBuilderService()
    {
        $payum = $this->getContainer()->get('payum.builder');

        $this->assertInstanceOf(PayumBuilder::class, $payum);
    }

    public function testShouldAllowGetPayumService()
    {
        $payum = $this->getPayum();

        $this->assertInstanceOf(Payum::class, $payum);
    }

    public function testShouldAllowGetGatewayConfigStorageAsService()
    {
        $storage = $this->getGatewayConfigStorage();

        $this->assertInstanceOf(GatewayConfigStorage::class, $storage);
        $this->assertInstanceOf(Storage::class, $storage);
    }

    public function testShouldAllowGetTokenStorageFromPayumService()
    {
        $this->assertInstanceOf(YadmStorage::class, $this->getPayum()->getTokenStorage());
    }

    public function testShouldAllowGetPaymentStorageFromPayumService()
    {
        $this->assertInstanceOf(YadmStorage::class, $this->getPayum()->getStorage(Payment::class));
    }

    public function testShouldAllowGetTokenFactoryFromPayumService()
    {
        $this->assertInstanceOf(GenericTokenFactory::class, $this->getPayum()->getTokenFactory());
    }
}