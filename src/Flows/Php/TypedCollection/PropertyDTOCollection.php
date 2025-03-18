<?php

namespace Xwero\Codestarter\Flows\Php\TypedCollection;


use Xwero\Codestarter\Flows\Php\DTO\PropertyDTO;
use Xwero\Codestarter\TypedCollection;

class PropertyDTOCollection extends TypedCollection
{
    protected function getAllowedType(): string
    {
        return PropertyDTO::class;
    }
}