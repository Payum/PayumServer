<?php
declare(strict_types=1);

namespace App\Util;

final class StringUtil
{
    public static function camelCaseToWords(string $camelCase) : string
    {
        $regExp = '/
          (?<=[a-z])
          (?=[A-Z])
        | (?<=[A-Z])
          (?=[A-Z][a-z])
        /x';

        return implode(' ', preg_split($regExp, $camelCase));
    }

    public static function lowerCaseToWords(string $lowerCase) : string
    {
        return str_replace('_', ' ', $lowerCase);
    }

    public static function nameToTitle(string $name) : string
    {
        return ucfirst(self::lowerCaseToWords(self::camelCaseToWords($name)));
    }
}
