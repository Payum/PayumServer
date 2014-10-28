<?php
namespace Payum\Server\Storage;

use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage as BaseDoctrineStorage;

class DoctrineStorage extends BaseDoctrineStorage implements StorageInterface
{
    /**
     * {@inheritDoc}
     */
    public function findAll()
    {
        return $this->objectManager->getRepository($this->modelClass)->findAll();
    }
}