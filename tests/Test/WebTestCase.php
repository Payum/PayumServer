<?php
declare(strict_types=1);

namespace App\Test;

use App\Model\GatewayConfig;
use App\Model\Payment;
use App\Model\SecurityToken;
use App\Storage\YadmStorage;
use Payum\Core\Payum;
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
        $storage = $this->getGatewayConfigStorage();
        $storage->getCollection()->drop();

        /** @var PaymentStorage $storage */
        $storage = $this->getPaymentStorage();
        $storage->getCollection()->drop();

        /** @var Storage $storage */
        $storage = $this->getTokenStorage();
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

    /**
     * @return Payum | object
     */
    protected function getPayum() : Payum
    {
        return $this->getContainer()->get('payum');
    }

    /**
     * @return Storage
     */
    protected function getGatewayConfigStorage() : Storage
    {
        /** @var YadmStorage $yadmStorage */
        $yadmStorage = $this->getPayum()->getStorage(GatewayConfig::class);

        return $yadmStorage->getStorage();
    }

    /**
     * @return Storage
     */
    protected function getPaymentStorage() : Storage
    {
        /** @var YadmStorage $yadmStorage */
        $yadmStorage = $this->getPayum()->getStorage(Payment::class);

        return $yadmStorage->getStorage();
    }

    /**
     * @return Storage
     */
    protected function getTokenStorage() : Storage
    {
        /** @var YadmStorage $yadmStorage */
        $yadmStorage = $this->getPayum()->getStorage(SecurityToken::class);

        return $yadmStorage->getStorage();
    }
}