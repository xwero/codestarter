<?php

namespace Xwero\Codestarter;

trait Devable
{
    use Pathable;
    use Composerable;

    const DEV_NAMESPACE = 'Xwero\\Codestarter\\Dev\\';
    protected string $devPath = '';

    protected array $groups = [];

    protected array $config = [];

    protected function setDevpath(int $pathsUp = 1): void
    {
        $path = $this->getPath('', $pathsUp);
        $devPath = $this->getPathFromComposerNamespace($path, self::DEV_NAMESPACE);

        $this->devPath = substr($devPath, 0,-1);
    }

    protected function setConfig(string $FlowPath) {
        $configpath = $this->devPath . '/Flows/' .$FlowPath . '/config.php';
        if(file_exists($configpath)) {
            $this->config = include $configpath;
        }
    }

    protected function setGroups(): void
    {
        $groups = [];

        foreach (glob($this->devPath . '/Flows/*', GLOB_ONLYDIR) as $dir) {
            $groups[] = str_replace($this->devPath.'/Flows/', '', $dir);
        }

        $this->groups = $groups;
    }
}