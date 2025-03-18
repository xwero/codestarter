<?php

namespace Xwero\Codestarter\Flows\Templates;

use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Xwero\Codestarter\Flows\AbstractFlow;
use Xwero\Codestarter\DTO\GenerateDTO;
use Xwero\Codestarter\DTO\NewFileDTO;

class TypeFlow extends AbstractFlow
{

    public array $types = ['twig', 'blade'];

    public function __construct(Filesystem $filesystem, InputInterface $input, OutputInterface $output, HelperInterface $helper)
    {
        parent::__construct($filesystem, $input, $output, $helper);

        $this->setConfig('Templates');
    }

    public function configChecks(OutputInterface $output): bool
    {
        if(!isset($this->config['root_path'])) {
            $output->writeln("<error>The root path is not in the config file.</error>");
            return false;
        }
    }

    public function generate(GenerateDTO $props): NewFileDTO
    {
        $pathAnswer = $this->getSinglelineFromQuestion('What is the file path?');

        $newFile = $this->{$props->type . 'Flow'}($props);

        $path = $pathAnswer === '' ? '/' : '/'.$pathAnswer.'/';

        $newFile->path = $this->config['root_path'].$path.$newFile->path;

        return $newFile;
    }

    protected function twigFlow(GenerateDTO $props): NewFileDTO
    {
        $contentAsnwer = $this->getMultilineFromQuestion('What is the content?');
        $content = new Content($contentAsnwer);

        return new NewFileDTO($props->filename.'.twig', $content->getGeneratedContent());
    }

    protected function bladeFlow(GenerateDTO $props, string $path): NewFileDTO
    {
        $contentAsnwer = $this->getMultilineFromQuestion('What is the content?');
        $content = new Content($contentAsnwer);

        return new NewFileDTO($props->filename.'.blade.php', $content->getGeneratedContent());
    }
}