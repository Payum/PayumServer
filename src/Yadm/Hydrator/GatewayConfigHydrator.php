<?php
declare(strict_types=1);

namespace App\Yadm\Hydrator;

use App\Model\GatewayConfig;
use Makasim\Yadm\Hydrator;

class GatewayConfigHydrator extends Hydrator
{
    public function __construct(string $modelClass)
    {
        parent::__construct(GatewayConfig::class);
    }
}