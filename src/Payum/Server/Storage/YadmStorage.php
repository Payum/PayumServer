<?php
namespace Payum\Server\Storage;

use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Makasim\Yadm\Storage as Storage;

class YadmStorage extends AbstractStorage
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * {@inheritdoc}
     *
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        parent::__construct(get_class($storage->create()));

        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpdateModel($model)
    {
        $values = \Makasim\Values\get_values($model);
        if (isset($values['_id'])) {
            $this->storage->update($model);
        } else {
            $this->storage->insert($model);
        }

    }

    /**
     * {@inheritdoc}
     */
    protected function doDeleteModel($model)
    {
        $this->storage->delete($model);
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
        return $this->storage->findOne(['self.id' => (string) $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        return iterator_to_array($this->storage->find($criteria));
    }

    /**
     * @param object $model
     * @param bool $strict
     *
     * @return string
     */
    protected function getModelId($model, $strict = true)
    {
        if ($strict && false == $model->getId()) {
            throw new \LogicException('The id is missing');
        }

        return (string) $model->getId();
    }
}