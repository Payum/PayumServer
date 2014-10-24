<?php
namespace Payum\Server\Controller;

use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class ApiStorageControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetAllStorages()
    {
        $this->getClient()->request('GET', '/api/configs/storages');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('storages', $content);

        $this->assertObjectHasAttribute('order', $content->storages);
        $this->assertObjectHasAttribute('security_token', $content->storages);
    }

    /**
     * @test
     */
    public function shouldAllowGetOrderStorage()
    {
        $this->getClient()->request('GET', '/api/configs/storages/order');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('storage', $content);

        $this->assertObjectHasAttribute('modelClass', $content->storage);
        $this->assertEquals('Payum\Server\Model\Order', $content->storage->modelClass);

        $this->assertObjectHasAttribute('idProperty', $content->storage);
        $this->assertEquals('number', $content->storage->idProperty);

        $this->assertObjectHasAttribute('factory', $content->storage);
        $this->assertEquals('filesystem', $content->storage->factory);
    }

    /**
     * @test
     */
    public function shouldAllowGetSecurityTokenStorage()
    {
        $this->getClient()->request('GET', '/api/configs/storages/security_token');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('storage', $content);

        $this->assertObjectHasAttribute('modelClass', $content->storage);
        $this->assertEquals('Payum\Server\Model\SecurityToken', $content->storage->modelClass);

        $this->assertObjectHasAttribute('idProperty', $content->storage);
        $this->assertEquals('hash', $content->storage->idProperty);

        $this->assertObjectHasAttribute('factory', $content->storage);
        $this->assertEquals('filesystem', $content->storage->factory);
    }
}
