<?php
namespace Payum\Server\Tests\Functional\Api\Controller;

use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class GatewayMetaControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetAllMetasOfStorages()
    {
        $this->getClient()->request('GET', '/gateways/metas');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('metas', $content);
        $this->assertObjectHasAttribute('generic', $content);

        $this->assertObjectHasAttribute('paypal_express_checkout', $content->metas);
        $this->assertObjectHasAttribute('stripe_js', $content->metas);
        $this->assertObjectHasAttribute('stripe_checkout', $content->metas);
    }
}
