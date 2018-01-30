<?php
declare(strict_types=1);

namespace App\Tests\Storage;

use Makasim\Yadm\Storage;
use App\Storage\GatewayConfigStorage;
use App\Storage\YadmStorage;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class YadmStorageTest
 * @package App\Tests\Storage
 */
class YadmStorageTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testShouldImplementsStorageInterface()
    {
        $rc = new ReflectionClass(YadmStorage::class);

        $this->assertTrue($rc->implementsInterface(GatewayConfigStorage::class));
    }

    /**
     * @return void
     */
    public function testCouldBeConstructedWithYadmStorageAsFirstArgument() : void
    {
        $storageMock = $this->createMock(Storage::class);

        new YadmStorage($storageMock, YadmStorage::DEFAULT_ID_PROPERTY, Storage::class);
    }
}