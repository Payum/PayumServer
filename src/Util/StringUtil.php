<?php
namespace App\Util;

final class StringUtil
{
    /**
     * @param string $camelCase
     *
     * @return string
     */
    public static function camelCaseToWords($camelCase)
    {
        $regExp = '/
          (?<=[a-z])
          (?=[A-Z])
        | (?<=[A-Z])
          (?=[A-Z][a-z])
        /x';

        return implode(' ', preg_split($regExp, $camelCase));
    }

    /**
     * @param string $lowerCase
     *
     * @return string
     */
    public static function lowerCaseToWords($lowerCase)
    {
        return str_replace('_', ' ', $lowerCase);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function nameToTitle($name)
    {
        return ucfirst(self::lowerCaseToWords(self::camelCaseToWords($name)));
    }
}
