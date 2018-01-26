<?php
declare(strict_types=1);

namespace App\Yadm\Hydrator;

use App\Model\SecurityToken;
use Makasim\Yadm\Hydrator;

/**
 * Class SecurityTokenHydrator
 * @package App\Yadm\Hydrator
 */
class SecurityTokenHydrator extends Hydrator
{
    /**
     * SecurityTokenHydrator constructor.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        parent::__construct(SecurityToken::class);
    }
}