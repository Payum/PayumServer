<?php
namespace Payum\Server\Tests\Functional\Mongo;

use Doctrine\ODM\MongoDB\DocumentManager;
use Payum\Server\Factory\Storage\FactoryInterface;
use Payum\Server\Model\Order;
use Payum\Server\Test\WebTestCase;

class OrderTest extends WebTestCase
{
    public function testShouldAllowPersistOrderToMongo()
    {
        /** @var FactoryInterface $factory */
        $factory = $this->app['payum.storage_factories']['doctrine_mongodb'];

        $storage = $factory->createStorage(Order::class, 'id', [
            'host' => 'localhost:27017',
            'databaseName' => 'payum_server_tests',
        ]);

        /** @var Order $order */
        $order = $storage->createModel();

        //guard
        $this->assertInstanceOf(Order::class, $order);
        $this->assertNull($order->getId());

        $order->setClientEmail('theClientEmail');
        $order->setClientId('theClientId');
        $order->setTotalAmount(123);
        $order->setCurrencyCode('USD');
        $order->setAfterUrl('theAfterUrl');
        $order->setDescription('theDesc');
        $order->setNumber('theNumber');
        $order->setPaymentName('thePaymentName');

        $storage->updateModel($order);

        $this->assertNotNull($order->getId());

        /** @var DocumentManager $dm */
        $dm = $this->readAttribute($storage, 'objectManager');
        $dm->clear();

        /** @var Order $foundOrder */
        $foundOrder = $storage->findModelById($order->getId());

        $this->assertInstanceOf(Order::class, $foundOrder);
        $this->assertNotSame($order, $foundOrder);
        $this->assertEquals($order->getId(), $foundOrder->getId());

        $this->assertEquals('theClientEmail', $foundOrder->getClientEmail());
        $this->assertEquals('theClientId', $foundOrder->getClientId());
    }

    public function testShouldAllowStorePaymentsDetails()
    {
        /** @var FactoryInterface $factory */
        $factory = $this->app['payum.storage_factories']['doctrine_mongodb'];

        $storage = $factory->createStorage(Order::class, 'id', [
            'host' => 'localhost:27017',
            'databaseName' => 'payum_server_tests',
        ]);

        /** @var Order $order */
        $order = $storage->createModel();

        //guard
        $this->assertInstanceOf(Order::class, $order);
        $this->assertNull($order->getId());

        $order->setClientEmail('theClientEmail');
        $order->setDetails(array('foo' => 'bar'));
        $order->setDetails(array('bar' => array('foo' => 'baz')));

        $expectedPayments = $order->getPayments();

        //guard
        $this->assertCount(2, $expectedPayments);

        $storage->updateModel($order);

        $this->assertNotNull($order->getId());

        /** @var DocumentManager $dm */
        $dm = $this->readAttribute($storage, 'objectManager');
        $dm->clear();

        /** @var Order $foundOrder */
        $foundOrder = $storage->findModelById($order->getId());

        $this->assertInstanceOf(Order::class, $foundOrder);
        $this->assertNotSame($order, $foundOrder);
        $this->assertEquals($order->getId(), $foundOrder->getId());

        $this->assertEquals($expectedPayments, $foundOrder->getPayments());
    }
}