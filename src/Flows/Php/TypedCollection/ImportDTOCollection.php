<?php

namespace Xwero\Codestarter\Flows\Php\TypedCollection;

use Xwero\Codestarter\Flows\Php\DTO\ImportDTO;
use Xwero\Codestarter\TypedCollection;

class ImportDTOCollection extends TypedCollection
{
    protected function getAllowedType(): string
    {
        return ImportDTO::class;
    }
}