<?php
declare(strict_types=1);

namespace App\Tests\Functional\Schema\Controller;

use App\Test\ResponseHelper;
use App\Test\WebTestCase;

class PaymentControllerTest extends WebTestCase
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
