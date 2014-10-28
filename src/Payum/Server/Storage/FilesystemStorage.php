<?php
namespace Payum\Server\Storage;

use Payum\Core\Storage\FilesystemStorage as BaseFilesystemStorage;

class FilesystemStorage extends BaseFilesystemStorage implements StorageInterface
{
    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return [];
    }
}