<?php

namespace Xwero\Codestarter;

trait Templatable
{
    /**
     * A helper method to get the code that needs to be generated.
     * The template knows the $content variable. Any public method can be called in the template.
     *
     * @param string $path
     * @param object $content
     * @return string
     */
    protected function getCodeFromTemplate(string $path, object $content): string
    {
        if (is_file($path)) {
            ob_start();
            include $path;
            return ob_get_clean();
        }
        return '';
    }
}