<?php
namespace Payum\Server\Storage;

use Makasim\Yadm\Hydrator;
use Makasim\Yadm\Storage;
use MongoDB\Collection;
use Payum\Server\Model\Payment;

/**
 * @method Payment create
 */
class PaymentStorage extends Storage
{
    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param Collection $collection
     * @param Hydrator $hydrator
     * @param null $pessimisticLock
     */
    public function __construct(Collection $collection, Hydrator $hydrator, $pessimisticLock = null)
    {
        parent::__construct($collection, $hydrator, $pessimisticLock);

        $this->hydrator = $hydrator;
        $this->collection = $collection;
    }

    /**
     * @param array $data
     * @param Payment|null $model
     *
     * @return Payment
     */
    public function hydrate(array $data, $model = null)
    {
        return $this->hydrator->hydrate($data, $model ?: $this->create());
    }

    /**
     * @param string $id
     *
     * @return Payment|null
     */
    public function findById($id)
    {
        return $this->findOne(['id' => $id]);
    }

    /**
     * @return Payment[]
     */
    public function findAll()
    {
        return $this->find([], [
            'sort' => ['createdAt.unix' => -1],
        ]);
    }
}
