<?php

namespace Xwero\Codestarter\Flows\Templates;

use Xwero\Codestarter\Templatable;

class Content
{
    use Templatable;
    public function __construct(
        public string $content,
    )
    {}

    public function getGeneratedContent(): string
    {
        $template = __DIR__ . '/templates/type.php';
        return $this->getCodeFromTemplate($template, $this);
    }
}