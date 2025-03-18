<?php

namespace Xwero\Codestarter\Flows\Php\DTO;

class CaseDTO
{
    public function __construct(
        public string $name,
        public string $value = '',
    )
    {}
}