<?php
namespace Payum\Server\Test;

abstract class ClientTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->createClient();
    }

    public function tearDown()
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
     * {@inheritDoc}
     */
    public function createClient(array $server = array())
    {
        return new Client($this->app, $server);
    }
}