<?php
declare(strict_types=1);

namespace App\Util;

use Ramsey\Uuid\Uuid as RamseyUuid;

class UUID
{
    public static function generate() : string
    {
        return RamseyUuid::uuid4()->toString();
    }
}
