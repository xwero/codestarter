<?php

namespace Xwero\Codestarter\Flows\Php\DTO;

class AttributeDTO
{
    public function __construct(
        public string $class,
        public string $import,
        public string $arguments = '',
    )
    {
    }
}