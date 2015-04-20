<?php
namespace Payum\Server\Api\Controller;

use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class PaymentMetaControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetOrder()
    {
        $this->getClient()->request('GET', '/api/payments/meta');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('meta', $content);

        $this->assertObjectHasAttribute('totalAmount', $content->meta);
        $this->assertObjectHasAttribute('currencyCode', $content->meta);
        $this->assertObjectHasAttribute('paymentName', $content->meta);
        $this->assertObjectHasAttribute('clientEmail', $content->meta);
        $this->assertObjectHasAttribute('clientId', $content->meta);
    }
}
