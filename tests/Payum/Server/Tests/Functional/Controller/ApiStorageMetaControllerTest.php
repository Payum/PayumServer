<?php
namespace Payum\Server\Controller;

use Payum\Server\Test\ClientTestCase;
use Payum\Server\Test\ResponseHelper;

class ApiStorageMetaControllerTest extends ClientTestCase
{
    use ResponseHelper;

    /**
     * @test
     */
    public function shouldAllowGetAllMetasOfStorages()
    {
        $this->getClient()->request('GET', '/api/configs/storages/metas');

        $this->assertClientResponseStatus(200);
        $this->assertClientResponseContentJson();

        $content = $this->getClientResponseJsonContent();

        $this->assertObjectHasAttribute('metas', $content);

        $this->assertObjectHasAttribute('filesystem', $content->metas);
        $this->assertObjectHasAttribute('doctrine_mongodb', $content->metas);
    }
}
