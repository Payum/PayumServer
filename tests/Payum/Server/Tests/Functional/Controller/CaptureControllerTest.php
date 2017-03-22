<?php
namespace Payum\Server\Tests\Functional\Controller;

use Makasim\Yadm\Storage;
use Payum\Core\Payum;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Model\Payment;
use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class CaptureControllerTest extends ClientTestCase
{
    use ResponseHelper;

    public function testShouldAllowChooseGateway()
    {

        /** @var Storage $gatewayConfigStorage */
        $gatewayConfigStorage = $this->app['payum.gateway_config_storage'];

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setFactoryName('offline');
        $gatewayConfig->setGatewayName('FooGateway');
        $gatewayConfig->setConfig(['factory' => 'offline']);
        $gatewayConfigStorage->insert($gatewayConfig);

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setFactoryName('offline');
        $gatewayConfig->setGatewayName('BarGateway');
        $gatewayConfig->setConfig(['factory' => 'offline']);
        $gatewayConfigStorage->insert($gatewayConfig);

        /** @var Storage $storage */
        $storage = $this->app['payum.payment_storage'];

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setGatewayName(null);
        $payment->setId(uniqid());

        $storage->insert($payment);

        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $token = $payum->getTokenFactory()->createCaptureToken('', $payment, 'http://localhost');

        $crawler = $this->getClient()->request('GET', $token->getTargetUrl());

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentHtml();

        $this->assertGreaterThan(0, count($crawler->filter('.payum-choose-gateway')));
        $this->assertContains('FooGateway', $crawler->text());
        $this->assertContains('BarGateway', $crawler->text());

        $form = $crawler->filter('form')->form();
        $form['gatewayName'] = 'BarGateway';

        $crawler = $this->getClient()->submit($form);

        $this->assertClientResponseStatus(302);
        $this->assertClientResponseRedirectionStartsWith('http://localhost?payum_token=');
    }

    public function testShouldObtainMissingDetails()
    {
        /** @var Storage $gatewayConfigStorage */
        $gatewayConfigStorage = $this->app['payum.gateway_config_storage'];

        /** @var GatewayConfig $gatewayConfig */
        $gatewayConfig = $gatewayConfigStorage->create();
        $gatewayConfig->setFactoryName('be2bill_offsite');
        $gatewayConfig->setGatewayName('be2bill');
        $gatewayConfig->setConfig([
            'factory' => 'be2bill_offsite',
            'identifier' => 'foo',
            'password' => 'bar',
            'sandbox' => true
        ]);
        $gatewayConfigStorage->insert($gatewayConfig);

        /** @var Payum $payum */
        $payum = $this->app['payum'];

        /** @var Storage $storage */
        $storage = $this->app['payum.payment_storage'];

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setGatewayName('be2bill');
        $payment->setId(uniqid());

        $storage->insert($payment);

        $token = $payum->getTokenFactory()->createCaptureToken('be2bill', $payment, 'http://localhost');

        $crawler = $this->getClient()->request('GET', $token->getTargetUrl());

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentHtml();

        $this->assertGreaterThan(0, count($crawler->filter('.payum-obtain-missing-details')));

        $form = $crawler->filter('form')->form();
        $form['payer[email]'] = 'foo@example.com';

        $crawler = $this->getClient()->submit($form);

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentHtml();

        $this->assertContains('Redirecting to payment page...', $crawler->text());
    }
}