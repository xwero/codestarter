<?php

namespace Xwero\Codestarter\Flows\Php\TypedCollection;

use Xwero\Codestarter\Flows\Php\DTO\AttributeDTO;
use Xwero\Codestarter\TypedCollection;

class AttributeDTOCollection extends TypedCollection
{

    protected function getAllowedType(): string
    {
        return AttributeDTO::class;
    }
}