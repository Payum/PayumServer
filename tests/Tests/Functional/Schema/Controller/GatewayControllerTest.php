<?php
declare(strict_types=1);

namespace App\Tests\Functional\Schema\Controller;

use App\Test\ResponseHelper;
use App\Test\WebTestCase;

class GatewayControllerTest extends WebTestCase
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
