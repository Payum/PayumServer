<?php
namespace Payum\Server\Tests\Functional\Controller;

use Makasim\Yadm\Storage;
use Payum\Core\Payum;
use Payum\Server\Model\GatewayConfig;
use Payum\Server\Model\Payment;
use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class AuthorizeControllerTest extends ClientTestCase
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

        /** @var Payum $payum */
        $payum = $this->app['payum'];

        /** @var Storage $storage */
        $storage = $this->app['payum.payment_storage'];

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setGatewayName(null);
        $payment->setId(uniqid());

        $storage->insert($payment);

        $token = $payum->getTokenFactory()->createAuthorizeToken('itDoesNotMatter', $payment, getenv('PAYUM_HTTP_HOST') . '');

        $crawler = $this->getClient()->request('GET', $token->getTargetUrl());

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentHtml();

        $this->assertGreaterThan(0, count($crawler->filter('.payum-choose-gateway')));
        $this->assertContains('FooGateway', $crawler->text());
        $this->assertContains('BarGateway', $crawler->text());

        $form = $crawler->filter('form')->form();

        $form['gatewayName'] = 'BarGateway';

        $this->getClient()->submit($form);

        $this->assertClientResponseStatus(302);
        $this->assertClientResponseRedirectionStartsWith(getenv('PAYUM_HTTP_HOST') . '?payum_token=');
    }
}