<?php
declare(strict_types=1);

namespace App\Tests\Storage;

use Makasim\Yadm\Storage;
use App\Storage\YadmStorage;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class YadmStorageTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testShouldImplementsStorageInterface()
    {
        $rc = new ReflectionClass(YadmStorage::class);

        $this->assertTrue($rc->implementsInterface(StorageInterface::class));
    }

    /**
     * @throws \ReflectionException
     */
    public function testCouldBeConstructedWithYadmStorageAsFirstArgument() : void
    {
        $storageMock = $this->createMock(Storage::class);

        $storage = new YadmStorage($storageMock, YadmStorage::DEFAULT_ID_PROPERTY, Storage::class);

        $rc = new ReflectionClass($storage);
        $this->assertTrue($rc->isInstance($storage));
    }
}