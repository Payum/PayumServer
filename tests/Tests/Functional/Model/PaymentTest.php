<?php
declare(strict_types=1);

namespace App\Tests\Functional\Model;

use App\Model\Payment;
use App\Test\WebTestCase;

/**
 * Class PaymentTest
 * @package App\Tests\Functional\Model
 */
class PaymentTest extends WebTestCase
{
    public function testShouldAllowPersistPaymentToMongo()
    {
        $storage = $this->getPaymentStorage();

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

        $storage->insert($payment);

        $this->assertNotNull($payment->getId());

        /** @var Payment $foundPayment */
        $foundPayment = $storage->findOne(['id' => $payment->getId()]);

        $this->assertInstanceOf(Payment::class, $foundPayment);
        $this->assertNotSame($payment, $foundPayment);
        $this->assertEquals($payment->getId(), $foundPayment->getId());

        $this->assertEquals('theClientEmail', $foundPayment->getClientEmail());
        $this->assertEquals('theClientId', $foundPayment->getClientId());
    }

    public function testShouldAllowStorePaymentsDetails()
    {
        $storage = $this->getPaymentStorage();

        /** @var Payment $payment */
        $payment = $storage->create();

        //guard
        $this->assertInstanceOf(Payment::class, $payment);

        $payment->setId(uniqid());
        $payment->setClientEmail('theClientEmail');
        $payment->setDetails(['foo' => 'bar']);
        $payment->setDetails(['bar' => ['foo' => 'baz']]);

        $storage->insert($payment);

        $this->assertNotNull($payment->getId());

        /** @var Payment $foundPayment */
        $foundPayment = $storage->findOne(['id' => $payment->getId()]);

        $this->assertInstanceOf(Payment::class, $foundPayment);
        $this->assertNotSame($payment, $foundPayment);
        $this->assertEquals($payment->getId(), $foundPayment->getId());
    }
}
