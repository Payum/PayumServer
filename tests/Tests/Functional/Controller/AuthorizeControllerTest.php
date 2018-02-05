<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Model\GatewayConfig;
use App\Model\Payment;
use App\Test\ResponseHelper;
use App\Test\WebTestCase;
use App\Util\UUID;

class AuthorizeControllerTest extends WebTestCase
{
    use ResponseHelper;

    public function testShouldAllowChooseGateway()
    {
        $gatewayConfigStorage = $this->getGatewayConfigStorage();

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

        $storage = $this->getPaymentStorage();

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setGatewayName(null);
        $payment->setId(UUID::generate());

        $storage->insert($payment);

        $token = $this->getPayum()
            ->getTokenFactory()
            ->createAuthorizeToken('itDoesNotMatter', $payment, getenv('PAYUM_HTTP_HOST'));

        $crawler = $this->getClient()->request('GET', $token->getTargetUrl());

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentHtml();

        $this->assertGreaterThan(0, count($crawler->filter('.payum-choose-gateway')));
        $this->assertContains('Foo Gateway', $crawler->text());
        $this->assertContains('Bar Gateway', $crawler->text());

        $form = $crawler->filter('form')->form();

        $form['gatewayName'] = 'BarGateway';

        $this->getClient()->submit($form);

        $this->assertClientResponseStatus(302);
        $this->assertClientResponseRedirectionStartsWith(getenv('PAYUM_HTTP_HOST') . '?payum_token=');
    }
}
