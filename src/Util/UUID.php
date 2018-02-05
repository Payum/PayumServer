<?php
declare(strict_types=1);

namespace App\Util;

use Ramsey\Uuid\Uuid as RamseyUuid;

class UUID
{
    /**
     * Generate 24-character hexadecimal string.
     *
     * @return string
     */
    public static function generate() : string
    {
        return RamseyUuid::uuid4()->toString();
    }
}
