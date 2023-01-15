<?php

namespace App\Util;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class StringUtil
{
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
}