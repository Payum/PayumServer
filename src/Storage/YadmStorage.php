<?php
declare(strict_types=1);

namespace App\Storage;

use LogicException;
use function Makasim\Yadm\get_object_id;
use function Makasim\Values\get_value;
use MongoDB\BSON\ObjectId;
use Makasim\Yadm\Storage;
use App\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;

class YadmStorage extends AbstractStorage
{
    const DEFAULT_ID_PROPERTY = '_id';

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var string
     */
    private $idProperty;

    public function __construct(Storage $storage, string $idProperty, string $modelClass)
    {
        parent::__construct($modelClass);

        $this->storage = $storage;
        $this->idProperty = $idProperty;
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpdateModel($model) : void
    {
        if (get_object_id($model, true)) {
            $this->storage->update($model);
        } else {
            $this->storage->insert($model);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doDeleteModel($model) : void
    {
        $this->storage->delete($model);
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetIdentity($model) : IdentityInterface
    {
        return Identity::createNew(get_class($model), $this->getModelId($model));
    }

    /**
     * {@inheritdoc}
     */
    protected function doFind($id)
    {
        if (static::DEFAULT_ID_PROPERTY === $this->idProperty) {
            $id = new ObjectId($id);
        }

        return $this->storage->findOne([$this->idProperty => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria) : array
    {
        return iterator_to_array($this->storage->find($criteria));
    }

    protected function getModelId($model, bool $strict = true) : string
    {
        if (static::DEFAULT_ID_PROPERTY === $this->idProperty) {
            $id = get_object_id($model, true);
        } else {
            $id = get_value($model, $this->idProperty);
        }

        if ($strict && !$id) {
            throw new LogicException('The id is missing');
        }

        return (string) $id;
    }
}
