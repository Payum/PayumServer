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
    public function shouldAllowGetGatewaysMeta()
    {
        $this->getClient()->request('GET', '/gateways/meta');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('meta', $content);

        $this->assertObjectHasAttribute('paypal_express_checkout', $content->meta);
        $this->assertObjectHasAttribute('stripe_js', $content->meta);
        $this->assertObjectHasAttribute('stripe_checkout', $content->meta);
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewaysGenericMeta()
    {
        $this->getClient()->request('GET', '/gateways/meta');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('generic', $content);

        $this->assertObjectHasAttribute('gatewayName', $content->generic);
        $this->assertObjectHasAttribute('label', $content->generic->gatewayName);
        $this->assertObjectHasAttribute('required', $content->generic->gatewayName);
        $this->assertObjectHasAttribute('type', $content->generic->gatewayName);
        $this->assertEquals('text', $content->generic->gatewayName->type);

        $this->assertObjectHasAttribute('factoryName', $content->generic);
        $this->assertObjectHasAttribute('label', $content->generic->factoryName);
        $this->assertObjectHasAttribute('required', $content->generic->factoryName);
        $this->assertObjectHasAttribute('choices', $content->generic->factoryName);
        $this->assertObjectHasAttribute('type', $content->generic->factoryName);
        $this->assertEquals('choice', $content->generic->factoryName->type);
    }
}
