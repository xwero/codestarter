<?php

namespace Xwero\Codestarter\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;

class AbstractCommand extends Command
{

    public function __construct(protected Filesystem $fs)
    {
        parent::__construct();
    }
}