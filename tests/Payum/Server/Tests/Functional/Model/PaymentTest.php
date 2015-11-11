<?php
namespace Payum\Server\Tests\Functional\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Payum\Core\Payum;
use Payum\Server\Factory\Storage\FactoryInterface;
use Payum\Server\Model\Payment;
use Payum\Server\Test\WebTestCase;

class PaymentTest extends WebTestCase
{
    public function testShouldAllowPersistPaymentToMongo()
    {
        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $storage = $payum->getStorage(Payment::class);

        /** @var Payment $payment */
        $payment = $storage->create();

        //guard
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNull($payment->getId());

        $payment->setId(uniqid());
        $payment->setClientEmail('theClientEmail');
        $payment->setClientId('theClientId');
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('USD');
        $payment->setDescription('theDesc');
        $payment->setNumber('theNumber');
        $payment->setGatewayName('theGatewayName');

        $storage->update($payment);

        $this->assertNotNull($payment->getId());

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
        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $storage = $payum->getStorage(Payment::class);

        /** @var Payment $payment */
        $payment = $storage->create();

        //guard
        $this->assertInstanceOf(Payment::class, $payment);

        $payment->setId(uniqid());
        $payment->setClientEmail('theClientEmail');
        $payment->setDetails(array('foo' => 'bar'));
        $payment->setDetails(array('bar' => array('foo' => 'baz')));

        $storage->update($payment);

        $this->assertNotNull($payment->getId());

        /** @var Payment $foundPayment */
        $foundPayment = $storage->find($payment->getId());

        $this->assertInstanceOf(Payment::class, $foundPayment);
        $this->assertNotSame($payment, $foundPayment);
        $this->assertEquals($payment->getId(), $foundPayment->getId());
    }
}