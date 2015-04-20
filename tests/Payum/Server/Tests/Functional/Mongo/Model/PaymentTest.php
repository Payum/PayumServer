<?php
namespace Payum\Server\Tests\Functional\Mongo;

use Doctrine\ODM\MongoDB\DocumentManager;
use Payum\Server\Factory\Storage\FactoryInterface;
use Payum\Server\Model\Payment;
use Payum\Server\Test\WebTestCase;

class PaymentTest extends WebTestCase
{
    public function testShouldAllowPersistPaymentToMongo()
    {
        /** @var FactoryInterface $factory */
        $factory = $this->app['payum.storage_factories']['doctrine_mongodb'];

        $storage = $factory->createStorage(Payment::class, 'id', [
            'host' => 'localhost:27017',
            'databaseName' => 'payum_server_tests',
        ]);

        /** @var Payment $payment */
        $payment = $storage->create();

        //guard
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNull($payment->getId());

        $payment->setClientEmail('theClientEmail');
        $payment->setClientId('theClientId');
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('USD');
        $payment->setAfterUrl('theAfterUrl');
        $payment->setDescription('theDesc');
        $payment->setNumber('theNumber');
        $payment->setGatewayName('thePaymentName');

        $storage->update($payment);

        $this->assertNotNull($payment->getId());

        /** @var DocumentManager $dm */
        $dm = $this->readAttribute($storage, 'objectManager');
        $dm->clear();

        /** @var Payment $foundPayment */
        $foundPayment = $storage->find($payment->getId());

        $this->assertInstanceOf(Payment::class, $foundPayment);
        $this->assertNotSame($payment, $foundPayment);
        $this->assertEquals($payment->getId(), $foundPayment->getId());

        $this->assertEquals('theClientEmail', $foundPayment->getClientEmail());
        $this->assertEquals('theClientId', $foundPayment->getClientId());
    }

    public function testShouldAllowStorePaymentsDetails()
    {
        /** @var FactoryInterface $factory */
        $factory = $this->app['payum.storage_factories']['doctrine_mongodb'];

        $storage = $factory->createStorage(Payment::class, 'id', [
            'host' => 'localhost:27017',
            'databaseName' => 'payum_server_tests',
        ]);

        /** @var Payment $payment */
        $payment = $storage->create();

        //guard
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNull($payment->getId());

        $payment->setClientEmail('theClientEmail');
        $payment->setDetails(array('foo' => 'bar'));
        $payment->setDetails(array('bar' => array('foo' => 'baz')));

        $expectedPayments = $payment->getPayments();

        //guard
        $this->assertCount(2, $expectedPayments);

        $storage->update($payment);

        $this->assertNotNull($payment->getId());

        /** @var DocumentManager $dm */
        $dm = $this->readAttribute($storage, 'objectManager');
        $dm->clear();

        /** @var Payment $foundPayment */
        $foundPayment = $storage->find($payment->getId());

        $this->assertInstanceOf(Payment::class, $foundPayment);
        $this->assertNotSame($payment, $foundPayment);
        $this->assertEquals($payment->getId(), $foundPayment->getId());

        $this->assertEquals($expectedPayments, $foundPayment->getPayments());
    }
}