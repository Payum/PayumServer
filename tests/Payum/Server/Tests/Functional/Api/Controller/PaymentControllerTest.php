<?php
namespace Payum\Server\Api\Controller;

use Payum\Server\Model\Payment;
use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class PaymentControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetOrder()
    {
        $order = new Payment();
        $order->setClientEmail('theExpectedOrder');

        $storage = $this->app['payum']->getStorage($order);
        $storage->update($order);

        $token = $this->app['payum.security.token_factory']->createToken('paypal_express_checkout', $order, 'order_get');

        $this->getClient()->request('GET', '/api/payments/'.$token->getHash());

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('order', $content);

        $this->assertObjectHasAttribute('clientEmail', $content->order);
        $this->assertEquals('theExpectedOrder', $content->order->clientEmail);
    }

    /**
     * @test
     */
    public function shouldAllowDeleteOrder()
    {
        $order = new Payment();
        $order->setClientEmail('theExpectedOrder');

        $storage = $this->app['payum']->getStorage($order);
        $storage->update($order);

        $token = $this->app['payum.security.token_factory']->createToken('paypal_express_checkout', $order, 'order_get');

        //guard
        $this->getClient()->request('GET', '/api/payments/'.$token->getHash());
        $this->assertClientResponseStatus(200);

        $this->getClient()->request('DELETE', '/api/payments/'.$token->getHash());
        $this->assertClientResponseStatus(204);

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $this->getClient()->request('GET', '/api/payments/'.$token->getHash());
    }

    /**
     * @test
     */
    public function shouldAllowUpdateOrder()
    {
        $order = new Payment();
        $order->setTotalAmount(123);
        $order->setClientEmail('theClientEmail@example.com');
        $order->setAfterUrl('http://example.com');

        $storage = $this->app['payum']->getStorage($order);
        $storage->update($order);

        $token = $this->app['payum.security.token_factory']->createToken('paypal_express_checkout', $order, 'order_get');

        //guard
        $this->getClient()->putJson('/api/payments/'.$token->getHash(), [
            'totalAmount' => 123,
            'currencyCode' => 'USD',
            'clientEmail' => 'theOtherClientEmail@example.com',
            'clientId' => 'theClientId',
            'paymentName' => 'stripe_js',
            'afterUrl' => 'http://example.com',
        ]);

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('order', $content);

        $this->assertObjectHasAttribute('clientEmail', $content->order);
        $this->assertEquals('theOtherClientEmail@example.com', $content->order->clientEmail);

        $this->assertObjectHasAttribute('totalAmount', $content->order);
        $this->assertEquals(123, $content->order->totalAmount);
    }

    /**
     * @test
     */
    public function shouldAllowCreateOrder()
    {
        $this->getClient()->postJson('/api/payments', [
            'totalAmount' => 123,
            'currencyCode' => 'USD',
            'clientEmail' => 'foo@example.com',
            'clientId' => 'theClientId',
            'paymentName' => 'stripe_js',
            'afterUrl' => 'http://example.com',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('order', $content);

        $this->assertObjectHasAttribute('clientEmail', $content->order);
        $this->assertEquals('foo@example.com', $content->order->clientEmail);

        $this->assertStringStartsWith('http://localhost/api/payments/', $this->getClient()->getResponse()->headers->get('Location'));
    }

    /**
     * @test
     */
    public function shouldAllowGetOrderLinks()
    {
        $this->getClient()->postJson('/api/payments', [
            'totalAmount' => 123,
            'currencyCode' => 'USD',
            'clientEmail' => 'foo@example.com',
            'clientId' => 'theClientId',
            'paymentName' => 'stripe_js',
            'afterUrl' => 'http://example.com',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        //guard
        $this->assertTrue($this->getClient()->getResponse()->headers->has('Location'));

        $this->getClient()->request('GET', $this->getClient()->getResponse()->headers->get('Location'));

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('order', $content);

        $this->assertObjectHasAttribute('_links', $content->order);
        $this->assertObjectHasAttribute('self', $content->order->_links);
    }
}
