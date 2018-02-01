<?php
declare(strict_types=1);

namespace App\Test;

use App\Storage\GatewayConfigStorage;
use App\Storage\PaymentTokenStorage;
use Payum\Core\Payum;
use Makasim\Values\HookStorage;
use Makasim\Yadm\Storage;
use App\Storage\PaymentStorage;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class WebTestCase extends SymfonyWebTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        HookStorage::clearAll();

        parent::setUp();

        $this->client = static::createClient([], [
            'HTTP_HOST' => getenv('PAYUM_SERVER_NAME'),
        ]);

        /** @var Storage $storage */
        $storage = $this->getGatewayConfigStorage();
        $storage->getCollection()->drop();

        /** @var PaymentStorage $storage */
        $storage = $this->getPaymentStorage();
        $storage->getCollection()->drop();

        /** @var Storage $storage */
        $storage = $this->getPaymentTokenStorage();
        $storage->getCollection()->drop();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->client = null;
    }

    /**
     * @return Client
     */
    protected function getClient() : Client
    {
        return $this->client;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer() : ContainerInterface
    {
        return $this->getClient()->getContainer();
    }

    /**
     * @return Payum | object
     */
    protected function getPayum() : Payum
    {
        return $this->getContainer()->get('payum');
    }

    protected function getGatewayConfigStorage() : GatewayConfigStorage
    {
        return $this->getContainer()->get(GatewayConfigStorage::class);
    }

    protected function getPaymentStorage() : PaymentStorage
    {
        return $this->getContainer()->get(PaymentStorage::class);
    }

    protected function getPaymentTokenStorage() : PaymentTokenStorage
    {
        return $this->getContainer()->get(PaymentTokenStorage::class);
    }
}