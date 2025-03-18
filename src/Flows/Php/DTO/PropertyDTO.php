<?php

namespace Xwero\Codestarter\Flows\Php\DTO;

use Xwero\Codestarter\Flows\Php\TypedCollection\ScalarOrClassDTOCollection;

class PropertyDTO
{
    public function __construct(
        public string    $name,
        public array     $prefixes = [],
        public ScalarOrClassDTOCollection     $types = new ScalarOrClassDTOCollection(),
        public mixed     $default = null,
    )
    {
    }
}