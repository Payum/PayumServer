<?php
declare(strict_types=1);

namespace App\Yadm\Hydrator;

use App\Model\Payment;
use Makasim\Yadm\Hydrator;

class PaymentHydrator extends Hydrator
{
    public function __construct(string $modelClass)
    {
        parent::__construct(Payment::class);
    }
}