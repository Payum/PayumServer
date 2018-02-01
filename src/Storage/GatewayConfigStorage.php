<?php
declare(strict_types=1);

namespace App\Storage;

use Makasim\Yadm\Storage;
use Makasim\Yadm\Hydrator;
use MongoDB\Collection;

/**
 * @method findOne(array $filter = [], array $options = []) : ?GatewayConfigInterface
 */
class GatewayConfigStorage extends Storage
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
