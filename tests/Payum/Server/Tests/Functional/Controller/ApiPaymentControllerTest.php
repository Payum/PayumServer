<?php
namespace Payum\Server\Controller;

use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class ApiPaymentControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetAllPayments()
    {
        $this->getClient()->request('GET', '/api/configs/payments');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('payments', $content);

        $this->assertObjectHasAttribute('paypal_express_checkout', $content->payments);
        $this->assertObjectHasAttribute('stripe_js', $content->payments);
        $this->assertObjectHasAttribute('stripe_checkout', $content->payments);
    }

    /**
     * @test
     */
    public function shouldAllowGetPaypalExpressCheckoutPayment()
    {
        $this->getClient()->request('GET', '/api/configs/payments/paypal_express_checkout');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('payment', $content);

        $this->assertObjectHasAttribute('factory', $content->payment);
        $this->assertEquals('paypal_express_checkout', $content->payment->factory);

        $this->assertObjectHasAttribute('name', $content->payment);
        $this->assertEquals('paypal_express_checkout', $content->payment->name);

        $this->assertObjectHasAttribute('options', $content->payment);
    }

    /**
     * @test
     */
    public function shouldAllowGetStripeJsPayment()
    {
        $this->getClient()->request('GET', '/api/configs/payments/stripe_js');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('payment', $content);

        $this->assertObjectHasAttribute('factory', $content->payment);
        $this->assertEquals('stripe_js', $content->payment->factory);

        $this->assertObjectHasAttribute('name', $content->payment);
        $this->assertEquals('stripe_js', $content->payment->name);

        $this->assertObjectHasAttribute('options', $content->payment);
    }

    /**
     * @test
     */
    public function shouldAllowGetStripeCheckoutPayment()
    {
        $this->getClient()->request('GET', '/api/configs/payments/stripe_checkout');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('payment', $content);

        $this->assertObjectHasAttribute('factory', $content->payment);
        $this->assertEquals('stripe_checkout', $content->payment->factory);

        $this->assertObjectHasAttribute('name', $content->payment);
        $this->assertEquals('stripe_checkout', $content->payment->name);

        $this->assertObjectHasAttribute('options', $content->payment);
    }
}
