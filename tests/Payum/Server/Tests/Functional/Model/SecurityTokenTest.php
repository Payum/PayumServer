<?php
namespace Payum\Server\Tests\Functional\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Payum\Core\Model\Identity;
use Payum\Core\Payum;
use Payum\Server\Factory\Storage\FactoryInterface;
use Payum\Server\Model\SecurityToken;
use Payum\Server\Test\WebTestCase;

class SecurityTokenTest extends WebTestCase
{
    public function testShouldAllowPersistSecurityTokenToMongo()
    {
        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $storage = $payum->getTokenStorage();

        /** @var SecurityToken $token */
        $token = $storage->create();

        //guard
        $this->assertInstanceOf(SecurityToken::class, $token);

        $token->setHash(uniqid());
        $token->setGatewayName('theGatewayName');
        $token->setTargetUrl('theTargetUrl');
        $token->setAfterUrl('theAfterUrl');

        $storage->update($token);

        $this->assertNotEmpty($token->getHash());

        /** @var SecurityToken $foundToken */
        $foundToken = $storage->find($token->getHash());

        $this->assertInstanceOf(SecurityToken::class, $foundToken);
        $this->assertNotSame($token, $foundToken);
        $this->assertEquals($token->getHash(), $foundToken->getHash());

        $this->assertEquals('theGatewayName', $foundToken->getGatewayName());
        $this->assertEquals('theTargetUrl', $foundToken->getTargetUrl());
        $this->assertEquals('theAfterUrl', $foundToken->getAfterUrl());
    }

    public function testShouldAllowStoreTokenDetails()
    {
        /** @var Payum $payum */
        $payum = $this->app['payum'];

        $storage = $payum->getTokenStorage();

        /** @var SecurityToken $token */
        $token = $storage->create();

        //guard
        $this->assertInstanceOf(SecurityToken::class, $token);

        $token->setHash(uniqid());
        $token->setGatewayName('theGatewayName');
        $token->setDetails($identity = new Identity('anId', 'stdClass'));

        $storage->update($token);

        $this->assertNotEmpty($token->getHash());

        /** @var SecurityToken $foundToken */
        $foundToken = $storage->find($token->getHash());

        $this->assertInstanceOf(SecurityToken::class, $foundToken);
        $this->assertNotSame($token, $foundToken);
        $this->assertEquals($token->getHash(), $foundToken->getHash());

        $this->assertEquals('theGatewayName', $foundToken->getGatewayName());

        $this->assertInstanceOf(Identity::class, $foundToken->getDetails());
        $this->assertNotSame($identity, $foundToken->getDetails());
        $this->assertEquals($identity->getId(), $foundToken->getDetails()->getId());
        $this->assertEquals($identity->getClass(), $foundToken->getDetails()->getClass());
    }
}