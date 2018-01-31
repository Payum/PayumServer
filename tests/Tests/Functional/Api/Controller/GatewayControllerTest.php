<?php
declare(strict_types=1);

namespace App\Tests\Functional\Api\Controller;

use App\Test\WebTestCase;
use Payum\Core\Model\GatewayConfigInterface;
use App\Test\ResponseHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class GatewayControllerTest
 * @package App\Tests\Functional\Api\Controller
 */
class GatewayControllerTest extends WebTestCase
{
    use ResponseHelper;

    public function setUp()
    {
        parent::setUp();

        $gatewayConfigStorage = $this->getGatewayConfigStorage();

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setGatewayName('paypal_express_checkout');
        $gatewayConfig->setFactoryName('paypal_express_checkout');
        $gatewayConfigStorage->insert($gatewayConfig);

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setGatewayName('stripe_js');
        $gatewayConfig->setFactoryName('stripe_js');
        $gatewayConfigStorage->insert($gatewayConfig);

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setGatewayName('stripe_checkout');
        $gatewayConfig->setFactoryName('stripe_checkout');
        $gatewayConfigStorage->insert($gatewayConfig);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $this->getClient()->postJson('/gateways/', [
            'gatewayName' => 'aGateway',
            'factoryName' => 'offline',
        ]);

        $this->assertClientResponseStatus(201);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateway', $content);

        $this->assertObjectHasAttribute('gatewayName', $content->gateway);
        $this->assertEquals('aGateway', $content->gateway->gatewayName);

        $this->assertObjectHasAttribute('factoryName', $content->gateway);
        $this->assertEquals('offline', $content->gateway->factoryName);

        $this->assertStringStartsWith(
            getenv('PAYUM_HTTP_HOST') . '/gateways/',
            $this->getClient()->getResponse()->headers->get('Location')
        );
    }

    /**
     * @test
     */
    public function shouldNotAllowCreateGatewayIfOneWithSameNameAlreadyExists()
    {
        $this->getClient()->postJson('/gateways/', [
            'gatewayName' => 'aUniqueGateway',
            'factoryName' => 'offline',
        ]);

        $this->assertClientResponseStatus(201);

        $this->getClient()->postJson('/gateways/', [
            'gatewayName' => 'aUniqueGateway',
            'factoryName' => 'offline',
        ]);

        $this->assertClientResponseStatus(400);
    }

    /**
     * @test
     */
    public function shouldAllowGetAllGateways()
    {
        $this->getClient()->request('GET', '/gateways/');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateways', $content);

        $this->assertObjectHasAttribute('paypal_express_checkout', $content->gateways);
        $this->assertObjectHasAttribute('stripe_js', $content->gateways);
        $this->assertObjectHasAttribute('stripe_checkout', $content->gateways);
    }

    /**
     * @test
     */
    public function shouldAllowGetPaypalExpressCheckoutGateway()
    {
        $this->getClient()->request('GET', '/gateways/paypal_express_checkout');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateway', $content);

        $this->assertObjectHasAttribute('factoryName', $content->gateway);
        $this->assertEquals('paypal_express_checkout', $content->gateway->factoryName);

        $this->assertObjectHasAttribute('gatewayName', $content->gateway);
        $this->assertEquals('paypal_express_checkout', $content->gateway->gatewayName);

        $this->assertObjectHasAttribute('config', $content->gateway);
    }

    /**
     * @test
     */
    public function shouldAllowGetStripeJsGateway()
    {
        $this->getClient()->request('GET', '/gateways/stripe_js');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateway', $content);

        $this->assertObjectHasAttribute('factoryName', $content->gateway);
        $this->assertEquals('stripe_js', $content->gateway->factoryName);

        $this->assertObjectHasAttribute('gatewayName', $content->gateway);
        $this->assertEquals('stripe_js', $content->gateway->gatewayName);

        $this->assertObjectHasAttribute('config', $content->gateway);
    }

    /**
     * @test
     */
    public function shouldAllowGetStripeCheckoutGateway()
    {
        $this->getClient()->request('GET', '/gateways/stripe_checkout');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('gateway', $content);

        $this->assertObjectHasAttribute('factoryName', $content->gateway);
        $this->assertEquals('stripe_checkout', $content->gateway->factoryName);

        $this->assertObjectHasAttribute('gatewayName', $content->gateway);
        $this->assertEquals('stripe_checkout', $content->gateway->gatewayName);

        $this->assertObjectHasAttribute('config', $content->gateway);
    }

    /**
     * @test
     */
    public function shouldAllowDeleteGateway()
    {
        $this->getClient()->request('DELETE', '/gateways/stripe_checkout');
        $this->assertClientResponseStatus(204);

        // @todo roll back expectException
//        $this->expectException(NotFoundHttpException::class);
        $this->getClient()->request('GET', '/gateways/stripe_checkout');
        $this->assertClientResponseStatus(404);
    }
}
