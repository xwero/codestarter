<?php

namespace Xwero\Codestarter\Flows\Php\TypedCollection;

use Xwero\Codestarter\Flows\Php\Method;
use Xwero\Codestarter\TypedCollection;

class MethodCollection extends TypedCollection
{
    protected function getAllowedType(): string
    {
        return Method::class;
    }
}