<?php
declare(strict_types=1);

namespace Payum\Server\Tests\Functional\Model;

use Makasim\Yadm\Storage;
use Payum\Core\Model\Identity;
use Payum\Server\Model\Payment;
use Payum\Server\Model\SecurityToken;
use Payum\Server\Test\WebTestCase;

class SecurityTokenTest extends WebTestCase
{
    public function testShouldAllowPersistSecurityTokenToMongo()
    {
        /** @var Storage $storage */
        $storage = $this->getContainer()->get('payum.security.token_storage');

        /** @var SecurityToken $token */
        $token = $storage->create();

        //guard
        $this->assertInstanceOf(SecurityToken::class, $token);

        $token->setHash(uniqid());
        $token->setTargetUrl('theTargetUrl');
        $token->setAfterUrl('theAfterUrl');

        $storage->insert($token);

        $this->assertNotEmpty($token->getHash());

        /** @var SecurityToken $foundToken */
        $foundToken = $storage->findOne(['hash' => $token->getHash()]);

        $this->assertInstanceOf(SecurityToken::class, $foundToken);
        $this->assertNotSame($token, $foundToken);
        $this->assertEquals($token->getHash(), $foundToken->getHash());

        $this->assertNull($foundToken->getGatewayName());
        $this->assertEquals('theTargetUrl', $foundToken->getTargetUrl());
        $this->assertEquals('theAfterUrl', $foundToken->getAfterUrl());
    }

    public function testShouldAllowStoreTokenDetails()
    {
        /** @var Storage $storage */
        $storage = $this->getContainer()->get('payum.security.token_storage');

        /** @var SecurityToken $token */
        $token = $storage->create();

        //guard
        $this->assertInstanceOf(SecurityToken::class, $token);

        $token->setHash(uniqid());
        $token->setGatewayName('theGatewayName');
        $token->setDetails($identity = new Identity('anId', 'stdClass'));

        $storage->insert($token);

        $this->assertNotEmpty($token->getHash());

        /** @var SecurityToken $foundToken */
        $foundToken = $storage->findOne(['hash' => $token->getHash()]);

        $this->assertInstanceOf(SecurityToken::class, $foundToken);
        $this->assertNotSame($token, $foundToken);
        $this->assertEquals($token->getHash(), $foundToken->getHash());

        $this->assertInstanceOf(Identity::class, $foundToken->getDetails());
        $this->assertNotSame($identity, $foundToken->getDetails());
        $this->assertEquals($identity->getId(), $foundToken->getDetails()->getId());
        $this->assertEquals($identity->getClass(), $foundToken->getDetails()->getClass());
    }

    public function testShouldGetsGatewayNameFromUnderlyingPaymentModel()
    {
        /** @var Storage $paymentStorage */
        $paymentStorage = $this->getContainer()->get('payum.payment_storage');

        /** @var Payment $payment */
        $payment = $paymentStorage->create();

        $payment->setId(uniqid());
        $payment->setGatewayName('theGatewayName');

        $paymentStorage->insert($payment);

        /** @var Storage $tokenStorage */
        $tokenStorage = $this->getContainer()->get('payum.security.token_storage');

        /** @var SecurityToken $token */
        $token = $tokenStorage->create();

        //guard
        $this->assertInstanceOf(SecurityToken::class, $token);

        $token->setHash(uniqid());
        $token->setGatewayName('theGatewayName');
        $token->setDetails($identity = new Identity($payment->getId(), $payment));

        $tokenStorage->insert($token);

        $this->assertNotEmpty($token->getHash());

        /** @var SecurityToken $foundToken */
        $foundToken = $tokenStorage->findOne(['hash' => $token->getHash()]);

        $this->assertInstanceOf(SecurityToken::class, $foundToken);
        $this->assertEquals('theGatewayName', $token->getGatewayName());
    }
}