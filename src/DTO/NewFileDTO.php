<?php

namespace Xwero\Codestarter\DTO;

class NewFileDTO
{
    public function __construct(
        public string $path,
        public string $content,
    )
    {
    }
}