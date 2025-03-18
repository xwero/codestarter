<?php

namespace Xwero\Codestarter;

trait Pathable
{
    protected function getPath($path, int $up = 2): string
    {
        if(is_dir(dirname(__DIR__, $up + 1).'/vendor'))
        {
            return dirname(__DIR__, $up + 1).'/'.$path;
        }

        return dirname(__DIR__, $up).'/'.$path;
    }
}