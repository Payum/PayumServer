<?php
declare(strict_types=1);

namespace App\Test;

use Makasim\Values\HookStorage;
use Makasim\Yadm\Storage;
use App\Storage\PaymentStorage;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebTestCase
 * @package App\Test
 */
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
        $storage = $this->getContainer()->get('payum.gateway_config_storage');
        $storage->getCollection()->drop();

        /** @var PaymentStorage $storage */
        $storage = $this->getContainer()->get('payum.payment_storage');
        $storage->getCollection()->drop();

        /** @var Storage $storage */
        $storage = $this->getContainer()->get('payum.security.token_storage');
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
    protected function getClient()
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
}