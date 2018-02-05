<?php
declare(strict_types=1);

namespace App\Tests\Functional\Model;

use App\Util\UUID;
use Payum\Core\Model\Identity;
use App\Model\Payment;
use App\Model\PaymentToken;
use App\Test\WebTestCase;

class PaymentTokenTest extends WebTestCase
{
    public function testShouldAllowPersistPaymentTokenToMongo()
    {
        $storage = $this->getPaymentTokenStorage();

        /** @var PaymentToken $token */
        $token = $storage->create();

        //guard
        $this->assertInstanceOf(PaymentToken::class, $token);

        $token->setHash(UUID::generate());
        $token->setTargetUrl('theTargetUrl');
        $token->setAfterUrl('theAfterUrl');

        $storage->insert($token);

        $this->assertNotEmpty($token->getHash());

        /** @var PaymentToken $foundToken */
        $foundToken = $storage->findOne(['hash' => $token->getHash()]);

        $this->assertInstanceOf(PaymentToken::class, $foundToken);
        $this->assertNotSame($token, $foundToken);
        $this->assertEquals($token->getHash(), $foundToken->getHash());

        $this->assertNull($foundToken->getGatewayName());
        $this->assertEquals('theTargetUrl', $foundToken->getTargetUrl());
        $this->assertEquals('theAfterUrl', $foundToken->getAfterUrl());
    }

    public function testShouldAllowStoreTokenDetails()
    {
        $storage = $this->getPaymentTokenStorage();

        /** @var PaymentToken $token */
        $token = $storage->create();

        //guard
        $this->assertInstanceOf(PaymentToken::class, $token);

        $token->setHash(UUID::generate());
        $token->setGatewayName('theGatewayName');
        $token->setDetails($identity = new Identity('anId', 'stdClass'));

        $storage->insert($token);

        $this->assertNotEmpty($token->getHash());

        /** @var PaymentToken $foundToken */
        $foundToken = $storage->findOne(['hash' => $token->getHash()]);

        $this->assertInstanceOf(PaymentToken::class, $foundToken);
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

        $payment->setId(UUID::generate());
        $payment->setGatewayName('theGatewayName');

        $paymentStorage->insert($payment);

        $tokenStorage = $this->getPaymentTokenStorage();

        /** @var PaymentToken $token */
        $token = $tokenStorage->create();

        //guard
        $this->assertInstanceOf(PaymentToken::class, $token);

        $token->setHash(UUID::generate());
        $token->setGatewayName('theGatewayName');
        $token->setDetails($identity = new Identity($payment->getId(), $payment));

        $tokenStorage->insert($token);

        $this->assertNotEmpty($token->getHash());

        /** @var PaymentToken $foundToken */
        $foundToken = $tokenStorage->findOne(['hash' => $token->getHash()]);

        $this->assertInstanceOf(PaymentToken::class, $foundToken);
        $this->assertEquals('theGatewayName', $token->getGatewayName());
    }
}
