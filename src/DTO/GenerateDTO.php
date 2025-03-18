<?php

namespace Xwero\Codestarter\DTO;

class GenerateDTO
{
    public function __construct(
        public string $type,
        public string $filename,
        // options from CLI
        public array $extraOptions = []
    )
    {
    }
}