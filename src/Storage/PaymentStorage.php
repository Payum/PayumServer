<?php
declare(strict_types=1);

namespace App\Storage;

use Makasim\Yadm\Hydrator;
use Makasim\Yadm\Storage;
use MongoDB\Collection;
use App\Model\Payment;
use Traversable;

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
     * @return Payment | object
     */
    public function hydrate(array $data, $model = null) : Payment
    {
        return $this->hydrator->hydrate($data, $model ?: $this->create());
    }

    /**
     * @param string $id
     *
     * @return Payment | null | object
     */
    public function findById($id) : ?Payment
    {
        return $this->findOne(['id' => $id]);
    }

    /**
     * @return Payment[] | Traversable
     */
    public function findAll() : Traversable
    {
        return $this->find([], [
            'sort' => ['createdAt.unix' => -1],
        ]);
    }
}
