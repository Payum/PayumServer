<?php
declare(strict_types=1);

namespace App\Storage;

use Makasim\Yadm\Hydrator;
use Makasim\Yadm\Storage;
use MongoDB\Collection;

class PaymentTokenStorage extends Storage
{
    /**
     * @param Collection $collection
     * @param Hydrator $hydrator
     * @param null $pessimisticLock
     */
    public function __construct(Collection $collection, Hydrator $hydrator, $pessimisticLock = null)
    {
        parent::__construct($collection, $hydrator, $pessimisticLock);
    }
}
