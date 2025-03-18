<?php

namespace Xwero\Codestarter;

trait Composerable
{
    use Pathable;

    protected string $rootPath = '';

    protected function getPathFromComposerNamespace(string $path, string $namespace): string
    {
        $json = json_decode(file_get_contents($path.'/composer.json'), true);
        $namespacePath = $json['autoload']['psr-4'][$namespace] ?? '';

        if($namespacePath === '') {
            return $namespacePath;
        }

        return $path . $namespacePath;
    }

    protected function getComposerContent(string $path) : array {
        return json_decode(file_get_contents($path.'/composer.json'), true);
    }

    protected function setComposerContent(string $path, array $content) : void
    {
        file_put_contents($path.'/composer.json', json_encode($content, JSON_PRETTY_PRINT));
    }
}