<?php

namespace Xwero\Codestarter\Flows\Php\DTO;

/**
 * When the import is empty the type is a scalar.
 */
class ScalarOrClassDTO
{
    public function __construct(
        public string $type,
        public string $import = ''
    )
    {
    }
}