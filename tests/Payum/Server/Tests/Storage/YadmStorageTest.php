<?php
namespace Payum\Server\Tests\Storage;

use Makasim\Yadm\Storage;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Storage\YadmStorage;

class YadmStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementsStorageInterface()
    {
        $rc = new \ReflectionClass(YadmStorage::class);

        $this->assertTrue($rc->implementsInterface(StorageInterface::class));
    }

    public function testCouldBeConstructedWithYadmStorageAsFirstArgument()
    {
        $storageMock = $this->createMock(Storage::class);

        new YadmStorage($storageMock);
    }
}