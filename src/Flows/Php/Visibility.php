<?php

namespace Xwero\Codestarter\Flows\Php;

enum Visibility : string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case PROTECTED = 'protected';
    case NO_VISIBILITY = '';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
