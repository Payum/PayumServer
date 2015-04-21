<?php
namespace Payum\Server\Tests\Functional\Api\Controller;

use Payum\Server\Model\Payment;
use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class PaymentControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetPayment()
    {
        $payment = new Payment();
        $payment->setClientEmail('theExpectedPayment');

        $storage = $this->app['payum']->getStorage($payment);
        $storage->update($payment);

        $token = $this->app['payum.security.token_factory']->createToken('paypal_express_checkout', $payment, 'payment_get');

        $this->getClient()->request('GET', '/payments/'.$token->getHash());

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('payment', $content);

        $this->assertObjectHasAttribute('clientEmail', $content->payment);
        $this->assertEquals('theExpectedPayment', $content->payment->clientEmail);
    }

    /**
     * @test
     */
    public function shouldAllowDeletePayment()
    {
        $payment = new Payment();
        $payment->setClientEmail('theExpectedPayment');

        $storage = $this->app['payum']->getStorage($payment);
        $storage->update($payment);

        $token = $this->app['payum.security.token_factory']->createToken('paypal_express_checkout', $payment, 'payment_get');

        //guard
        $this->getClient()->request('GET', '/payments/'.$token->getHash());
        $this->assertClientResponseStatus(200);

        $this->getClient()->request('DELETE', '/payments/'.$token->getHash());
        $this->assertClientResponseStatus(204);

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $this->getClient()->request('GET', '/payments/'.$token->getHash());
    }

    /**
     * @test
     */
    public function shouldAllowUpdatePayment()
    {
        $payment = new Payment();
        $payment->setTotalAmount(123);
        $payment->setClientEmail('theClientEmail@example.com');
        $payment->setAfterUrl('http://example.com');

        $storage = $this->app['payum']->getStorage($payment);
        $storage->update($payment);

        $token = $this->app['payum.security.token_factory']->createToken('paypal_express_checkout', $payment, 'payment_get');

        //guard
        $this->getClient()->putJson('/payments/'.$token->getHash(), [
            'totalAmount' => 123,
            'currencyCode' => 'USD',
            'clientEmail' => 'theOtherClientEmail@example.com',
            'clientId' => 'theClientId',
            'gatewayName' => 'stripe_js',
            'afterUrl' => 'http://example.com',
        ]);

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('payment', $content);

        $this->assertObjectHasAttribute('clientEmail', $content->payment);
        $this->assertEquals('theOtherClientEmail@example.com', $content->payment->clientEmail);

        $this->assertObjectHasAttribute('totalAmount', $content->payment);
        $this->assertEquals(123, $content->payment->totalAmount);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePayment()
    {
        $this->getClient()->postJson('/payments', [
            'totalAmount' => 123,
            'currencyCode' => 'USD',
            'clientEmail' => 'foo@example.com',
            'clientId' => 'theClientId',
            'gatewayName' => 'stripe_js',
            'afterUrl' => 'http://example.com',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('payment', $content);

        $this->assertObjectHasAttribute('clientEmail', $content->payment);
        $this->assertEquals('foo@example.com', $content->payment->clientEmail);

        $this->assertStringStartsWith('http://localhost/payments/', $this->getClient()->getResponse()->headers->get('Location'));
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentLinks()
    {
        $this->getClient()->postJson('/payments', [
            'totalAmount' => 123,
            'currencyCode' => 'USD',
            'clientEmail' => 'foo@example.com',
            'clientId' => 'theClientId',
            'gatewayName' => 'stripe_js',
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

        $this->assertObjectHasAttribute('payment', $content);

        $this->assertObjectHasAttribute('_links', $content->payment);
        $this->assertObjectHasAttribute('self', $content->payment->_links);
    }
}
