<?php

namespace Xwero\Codestarter\Flows\Php\TypedCollection;

use Xwero\Codestarter\Flows\Php\DTO\ScalarOrClassDTO;
use Xwero\Codestarter\TypedCollection;

class ScalarOrClassDTOCollection extends TypedCollection
{
    protected function getAllowedType(): string
    {
        return ScalarOrClassDTO::class;
    }
}