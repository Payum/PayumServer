<?php
declare(strict_types=1);

namespace App\Storage;

use LogicException;
use function Makasim\Yadm\get_object_id;
use function Makasim\Values\get_value;
use MongoDB\BSON\ObjectId;
use Makasim\Yadm\Storage;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;

/**
 * Class YadmStorage
 * @package App\Storage
 */
class YadmStorage extends AbstractStorage
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var string
     */
    private $idProperty;

    /**
     * @param Storage $storage
     * @param $idProperty
     * @param string $modelClass
     */
    public function __construct(Storage $storage, $idProperty, string $modelClass)
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
        return new Identity($this->getModelId($model), $model);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFind($id) : ?object
    {
        if ('_id' == $this->idProperty) {
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

    /**
     * @param object $model
     * @param bool $strict
     *
     * @return string
     */
    protected function getModelId($model, $strict = true) : string
    {
        if ('_id' === $this->idProperty) {
            $id = get_object_id($model, true);
        } else {
            $id = get_value($model, $this->idProperty);
        }

        if ($strict && false == $id) {
            throw new LogicException('The id is missing');
        }

        return (string) $id;
    }
}