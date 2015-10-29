<?php
namespace Payum\Server\Storage;

use Doctrine\MongoDB\Collection;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;

class MongoStorage extends AbstractStorage
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * {@inheritdoc}
     *
     * @param Collection $collection
     */
    public function __construct($modelClass, Collection $collection)
    {
        parent::__construct($modelClass);

        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpdateModel($model)
    {
        $values = \Makasim\Values\get_values($model);
        if (isset($values['_id'])) {
            $values['_id'] = (string) $values['_id'];
        }

        $values = json_decode(json_encode($values), true);

        if ($id = $this->getModelId($model, false)) {

            $valuesToSave = $values;
            unset($valuesToSave['_id']);

            $this->collection->update(['_id' => new \MongoId($id)], $valuesToSave);
        } else {
            $this->collection->insert($values);
        }

        if (isset($values['_id'])) {
            $values['_id'] = (string) $values['_id'];
        }

        \Makasim\Values\set_values($model, $values);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDeleteModel($model)
    {
        if ($id = $this->getModelId($model, false)) {
            $this->collection->remove(['_id' => new \MongoId($id)]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetIdentity($model)
    {
        return new Identity($this->getModelId($model), $model);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFind($id)
    {
        if ($values = $this->collection->findOne(['_id' => new \MongoId((string) $id)])) {
            return $this->hydrate($values);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        $models = [];
        foreach($this->collection->find($criteria) as $values) {
            $models[] = $this->hydrate($values);
        }

        return $models;
    }

    /**
     * @param object $model
     * @param bool $strict
     *
     * @return string
     */
    protected function getModelId($model, $strict = true)
    {
        $values = \Makasim\Values\get_values($model);

        $id = isset($values['_id']) ? $values['_id'] : null;
        if ($strict && false == $id) {
            throw new \LogicException('The id is missing');
        }

        return (string) $id;
    }

    /**
     * @param array $values
     *
     * @return object
     */
    protected function hydrate(array $values)
    {
        $model = $this->create();
        \Makasim\Values\set_values($model, $values);

        return $model;
    }
}