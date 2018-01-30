<?php
declare(strict_types=1);

namespace App\Yadm\Hydrator;

use App\Model\PaymentToken;
use Makasim\Yadm\Hydrator;

/**
 * Class PaymentTokenHydrator
 * @package App\Yadm\Hydrator
 */
class PaymentTokenHydrator extends Hydrator
{
    /**
     * PaymentTokenHydrator constructor.
     *
     * @param string $modelClass
     */
    public function __construct(string $modelClass)
    {
        parent::__construct(PaymentToken::class);
    }
}