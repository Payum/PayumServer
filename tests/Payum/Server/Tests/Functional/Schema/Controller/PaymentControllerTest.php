<?php
namespace App\Tests\Functional\Schema\Controller;

use App\Test\ClientTestCase;
use App\Test\ResponseHelper;

class PaymentControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetNewPaymentSchema()
    {
        $this->getClient()->request('GET', '/schema/payments/new.json');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJsonSchema();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('$schema', $content);
    }

    /**
     * @test
     */
    public function shouldAllowGetNewPaymentFormDefinition()
    {
        $this->getClient()->request('GET', '/schema/payments/form/new.json');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();
    }
}
