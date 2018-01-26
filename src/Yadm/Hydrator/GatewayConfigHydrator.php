<?php
declare(strict_types=1);

namespace App\Yadm\Hydrator;

use App\Model\GatewayConfig;
use Makasim\Yadm\Hydrator;

/**
 * Class GatewayConfigHydrator
 * @package App\Yadm\Hydrator
 */
class GatewayConfigHydrator extends Hydrator
{
    /**
     * GatewayConfigHydrator constructor.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        parent::__construct(GatewayConfig::class);
    }
}