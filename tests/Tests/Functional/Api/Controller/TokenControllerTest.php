<?php
declare(strict_types=1);

namespace App\Tests\Functional\Api\Controller;

use Makasim\Yadm\Storage;
use App\Model\GatewayConfig;
use App\Model\Payment;
use App\Test\ClientTestCase;
use App\Test\ResponseHelper;

/**
 * Class TokenControllerTest
 * @package App\Tests\Functional\Api\Controller
 */
class TokenControllerTest extends ClientTestCase
{
    use ResponseHelper;

    public function testShouldNotAllowCreateTokenWithNotSupportedType()
    {
        $this->getClient()->postJson('/tokens/', [
            'type' => 'notSupportedType',
            'paymentId' => 'aPaymentId',
            'afterUrl' => getenv('PAYUM_HTTP_HOST') . '/afterUrl',
        ]);

        $this->assertClientResponseStatus(400);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();
    }

    public function testShouldAllowCreateCaptureToken()
    {
        /** @var Storage $gatewayConfigStorage */
        $gatewayConfigStorage = $this->getGatewayConfigStorage();

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setFactoryName('offline');
        $gatewayConfig->setGatewayName('offline');
        $gatewayConfig->setConfig([]);
        $gatewayConfigStorage->insert($gatewayConfig);

        /** @var Storage $storage */
        $storage = $this->getPaymentStorage();

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setGatewayName('offline');
        $payment->setId(uniqid());

        $storage->insert($payment);

        $this->getClient()->postJson('/tokens/', [
            'type' => 'capture',
            'paymentId' => $payment->getId(),
            'afterUrl' => getenv('PAYUM_HTTP_HOST') . '/afterUrl',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('token', $content);
        $token = $content->token;

        $this->assertNotEmpty($token->hash);
        $this->assertEquals($payment->getId(), $token->paymentId);
        $this->assertEquals(getenv('PAYUM_HTTP_HOST') . '/afterUrl?paymentId=' . $payment->getId(), $token->afterUrl);
        $this->assertStringStartsWith(getenv('PAYUM_HTTP_HOST') . '/payment/capture', $token->targetUrl);

        $this->getClient()->request('GET', $token->targetUrl);

        $this->assertClientResponseStatus(302);
        $this->assertClientResponseRedirection($token->afterUrl);
    }

    public function testShouldAllowCreateAuthorizeToken()
    {
        /** @var Storage $gatewayConfigStorage */
        $gatewayConfigStorage = $this->getGatewayConfigStorage();

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setFactoryName('offline');
        $gatewayConfig->setGatewayName('offline');
        $gatewayConfig->setConfig([]);
        $gatewayConfigStorage->insert($gatewayConfig);

        /** @var Storage $storage */
        $storage = $this->getPaymentStorage();

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setGatewayName('offline');
        $payment->setId(uniqid());

        $storage->insert($payment);

        $this->getClient()->postJson('/tokens/', [
            'type' => 'authorize',
            'paymentId' => $payment->getId(),
            'afterUrl' => getenv('PAYUM_HTTP_HOST') . '/afterUrl',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('token', $content);
        $token = $content->token;

        $this->assertNotEmpty($token->hash);
        $this->assertEquals($payment->getId(), $token->paymentId);
        $this->assertEquals(getenv('PAYUM_HTTP_HOST') . '/afterUrl?paymentId=' . $payment->getId(), $token->afterUrl);
        $this->assertStringStartsWith(getenv('PAYUM_HTTP_HOST') . '/payment/authorize', $token->targetUrl);

        $this->getClient()->request('GET', $token->targetUrl);

        $this->assertClientResponseStatus(302);
        $this->assertClientResponseRedirection($token->afterUrl);
    }
}
