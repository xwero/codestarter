<?php

namespace Xwero\Codestarter\Flows\Php\DTO;

use Xwero\Codestarter\Flows\Php\Type;

/**
 * Used to create types when it is wanted
 */
class ImportDTO
{
    public function __construct(
        public string $path,
        public Type $type,
    )
    {
    }
}