<?php
namespace Payum\Server\Tests\Functional\Api\Controller;

use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Model\Payment;
use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TokenControllerTest extends ClientTestCase
{
    use ResponseHelper;

    public function testShouldNotAllowCreateTokenWithNotSupportedType()
    {
        $this->getClient()->postJson('/tokens/', [
            'type' => 'notSupportedType',
            'paymentId' => 'aPaymentId',
            'afterUrl' => 'http://localhost/afterUrl',
        ]);

        $this->assertClientResponseStatus(400);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();
    }

    public function testShouldAllowCreateCaptureToken()
    {
        /** @var StorageInterface $gatewayConfigStorage */
        $gatewayConfigStorage = $this->app['payum.yadm_gateway_config_storage'];

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setFactoryName('offline');
        $gatewayConfig->setGatewayName('offline');
        $gatewayConfig->setConfig([]);
        $gatewayConfigStorage->update($gatewayConfig);

        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $store = $payum->getStorage(Payment::class);

        /** @var Payment $payment */
        $payment = $store->create();
        $payment->setGatewayName('offline');
        $payment->setId(uniqid());

        $store->update($payment);

        $this->getClient()->postJson('/tokens/', [
            'type' => 'capture',
            'paymentId' => $payment->getId(),
            'afterUrl' => 'http://localhost/afterUrl',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('token', $content);
        $token = $content->token;

        $this->assertNotEmpty($token->hash);
        $this->assertEquals($payment->getId(), $token->paymentId);
        $this->assertEquals('http://localhost/afterUrl?paymentId='.$payment->getId(), $token->afterUrl);
        $this->assertStringStartsWith('http://localhost/payment/capture', $token->targetUrl);

        $this->getClient()->request('GET', $token->targetUrl);

        $this->assertClientResponseStatus(302);
        $this->assertClientResponseRedirection($token->afterUrl);
    }

    public function testShouldAllowCreateAuthorizeToken()
    {
        /** @var StorageInterface $gatewayConfigStorage */
        $gatewayConfigStorage = $this->app['payum.yadm_gateway_config_storage'];

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setFactoryName('offline');
        $gatewayConfig->setGatewayName('offline');
        $gatewayConfig->setConfig([]);
        $gatewayConfigStorage->update($gatewayConfig);

        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $store = $payum->getStorage(Payment::class);

        /** @var Payment $payment */
        $payment = $store->create();
        $payment->setGatewayName('offline');
        $payment->setId(uniqid());

        $store->update($payment);

        $this->getClient()->postJson('/tokens/', [
            'type' => 'authorize',
            'paymentId' => $payment->getId(),
            'afterUrl' => 'http://localhost/afterUrl',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('token', $content);
        $token = $content->token;

        $this->assertNotEmpty($token->hash);
        $this->assertEquals($payment->getId(), $token->paymentId);
        $this->assertEquals('http://localhost/afterUrl?paymentId='.$payment->getId(), $token->afterUrl);
        $this->assertStringStartsWith('http://localhost/payment/authorize', $token->targetUrl);

        $this->getClient()->request('GET', $token->targetUrl);

        $this->assertClientResponseStatus(302);
        $this->assertClientResponseRedirection($token->afterUrl);
    }
}
