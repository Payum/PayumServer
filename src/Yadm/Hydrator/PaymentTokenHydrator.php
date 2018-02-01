<?php
declare(strict_types=1);

namespace App\Yadm\Hydrator;

use App\Model\PaymentToken;
use Makasim\Yadm\Hydrator;

class PaymentTokenHydrator extends Hydrator
{
    public function __construct(string $modelClass)
    {
        parent::__construct(PaymentToken::class);
    }
}