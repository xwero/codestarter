<?php

namespace Xwero\Codestarter\Flows\Php\TypedCollection;

use Xwero\Codestarter\Flows\Php\DTO\CaseDTO;
use Xwero\Codestarter\TypedCollection;

class CaseDTOCollection extends TypedCollection
{
    protected function getAllowedType(): string
    {
        return CaseDTO::class;
    }
}