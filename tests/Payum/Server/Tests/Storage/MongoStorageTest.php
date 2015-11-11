<?php
namespace Payum\Server\Tests\Storage;

use Doctrine\MongoDB\Collection;
use Payum\Core\Storage\StorageInterface;
use Payum\Server\Storage\MongoStorage;

class MongoStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementsStorageInterface()
    {
        $rc = new \ReflectionClass(MongoStorage::class);

        $this->assertTrue($rc->implementsInterface(StorageInterface::class));
    }

    public function testCouldBeConstructedWithModelClassAndMongoCollection()
    {
        /** @var Collection $collection */
        $collection = $this->getMock(Collection::class, [], [], '', false);

        new MongoStorage(\stdClass::class, $collection);
    }
}