<?php
namespace Payum\Server\Tests\Functional\Api\Controller;

use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class PaymentMetaControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetPayment()
    {
        $this->getClient()->request('GET', '/payments/metas');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('meta', $content);

        $this->assertObjectHasAttribute('totalAmount', $content->meta);
        $this->assertObjectHasAttribute('currencyCode', $content->meta);
        $this->assertObjectHasAttribute('gatewayName', $content->meta);
        $this->assertObjectHasAttribute('clientEmail', $content->meta);
        $this->assertObjectHasAttribute('clientId', $content->meta);
    }
}
