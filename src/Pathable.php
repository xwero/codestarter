<?php

namespace Xwero\Codestarter;

trait Pathable
{
    protected function getPath($path, int $up = 2): string
    {
        $testpath = dirname(__DIR__);
        if(str_contains($testpath, 'vendor'))
        {
            $vendorUp = count(explode('/',strrchr($testpath, 'vendor/')));
            return dirname(__DIR__, $up + $vendorUp).'/'.$path;
        }

        return dirname(__DIR__, $up).'/'.$path;
    }
}