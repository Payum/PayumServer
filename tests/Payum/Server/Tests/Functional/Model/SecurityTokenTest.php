<?php
namespace Payum\Server\Tests\Functional\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Payum\Core\Model\Identity;
use Payum\Server\Factory\Storage\FactoryInterface;
use Payum\Server\Model\SecurityToken;
use Payum\Server\Test\WebTestCase;

class SecurityTokenTest extends WebTestCase
{
    public function testShouldAllowPersistSecurityTokenToMongo()
    {
        /** @var FactoryInterface $factory */
        $factory = $this->app['payum.storage_factories']['doctrine_mongodb'];

        $storage = $factory->createStorage(SecurityToken::class, 'hash', [
            'host' => 'localhost:27017',
            'databaseName' => 'payum_server_tests',
        ]);

        /** @var SecurityToken $token */
        $token = $storage->create();

        //guard
        $this->assertInstanceOf(SecurityToken::class, $token);

        $token->setGatewayName('theGatewayName');
        $token->setTargetUrl('theTargetUrl');
        $token->setAfterUrl('theAfterUrl');

        $storage->update($token);

        $this->assertNotEmpty($token->getHash());

        /** @var DocumentManager $dm */
        $dm = $this->readAttribute($storage, 'objectManager');
        $dm->clear();

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
        /** @var FactoryInterface $factory */
        $factory = $this->app['payum.storage_factories']['doctrine_mongodb'];

        $storage = $factory->createStorage(SecurityToken::class, 'hash', [
            'host' => 'localhost:27017',
            'databaseName' => 'payum_server_tests',
        ]);

        /** @var SecurityToken $token */
        $token = $storage->create();

        //guard
        $this->assertInstanceOf(SecurityToken::class, $token);

        $token->setGatewayName('theGatewayName');
        $token->setDetails($identity = new Identity('anId', 'stdClass'));

        $storage->update($token);

        $this->assertNotEmpty($token->getHash());

        /** @var DocumentManager $dm */
        $dm = $this->readAttribute($storage, 'objectManager');
        $dm->clear();

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