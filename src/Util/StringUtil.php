<?php

namespace App\Util;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class StringUtil
{
    public const LINE_LENGTH = 50;

    public static function camelCaseToSnakeCase($value): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->normalize($value);
    }

    public static function snakeCaseToCamelCase($value): ?string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->denormalize($value);
    }

    public static function createClassName($value, $prefix, $suffix): ?string
    {
        return sprintf('%s%s%s', $prefix, ucfirst(StringUtil::snakeCaseToCamelCase($value)), $suffix);
    }

    public static function lineFill($text, $filler): string
    {
        $length = (self::LINE_LENGTH - strlen($text) - 2) / 2;
        return sprintf('%s %s %s',
            str_repeat($filler, round($length, 0, PHP_ROUND_HALF_DOWN)),
            $text,
            str_repeat($filler, round($length))
        );
    }
}
