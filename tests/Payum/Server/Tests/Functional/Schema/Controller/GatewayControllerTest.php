<?php
namespace Payum\Server\Tests\Functional\Schema\Controller;

use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class GatewayControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetDefaultGatewaySchema()
    {
        $this->getClient()->request('GET', '/schema/gateways/default.json');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJsonSchema();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('$schema', $content);
    }

    /**
     * @test
     */
    public function shouldAllowGetRealGatewaySchema()
    {
        $this->getClient()->request('GET', '/schema/gateways/paypal_express_checkout.json');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJsonSchema();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('$schema', $content);
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultGatewayFormDefinition()
    {
        $this->getClient()->request('GET', '/schema/gateways/form/default.json');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();
    }

    /**
     * @test
     */
    public function shouldAllowGetRealGatewayFormDefinition()
    {
        $this->getClient()->request('GET', '/schema/gateways/form/paypal_express_checkout.json');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();
    }
}
