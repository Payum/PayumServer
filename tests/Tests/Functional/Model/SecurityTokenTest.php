<?php
declare(strict_types=1);

namespace App\Tests\Functional\Model;

use Makasim\Yadm\Storage;
use Payum\Core\Model\Identity;
use App\Model\Payment;
use App\Model\SecurityToken;
use App\Test\WebTestCase;

/**
 * Class SecurityTokenTest
 * @package App\Tests\Functional\Model
 */
class SecurityTokenTest extends WebTestCase
{
    public function testShouldAllowPersistSecurityTokenToMongo()
    {
        $storage = $this->getTokenStorage();

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
        $storage = $this->getTokenStorage();

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
        $paymentStorage = $this->getPaymentStorage();

        /** @var Payment $payment */
        $payment = $paymentStorage->create();

        $payment->setId(uniqid());
        $payment->setGatewayName('theGatewayName');

        $paymentStorage->insert($payment);

        $tokenStorage = $this->getTokenStorage();

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
