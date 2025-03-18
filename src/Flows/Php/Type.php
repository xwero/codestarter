<?php

namespace Xwero\Codestarter\Flows\Php;

enum Type : string
{
    case Object = 'class';
    case Interface = 'interface';
    case Trait  = 'trait';

    case Enum  = 'enum';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
