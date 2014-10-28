<?php
namespace Payum\Server\Storage;

use Payum\Core\Storage\StorageInterface as BaseStorageInterface;

interface StorageInterface extends BaseStorageInterface
{
    /**
     * @return object[]
     */
    function findAll();
}