<?php

namespace Xwero\Codestarter\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use WyriHaximus\Lister;
use Xwero\Codestarter\Devable;
use Xwero\Codestarter\Questionable;

#[AsCommand(name: 'setup')]
class SetupCommand extends AbstractCommand
{
    use Devable;
    use Questionable;

    public function __construct(Filesystem $fs)
    {
        parent::__construct($fs);
    }

    protected function configure()
    {
        $this->addOption('clear-dev', null, InputOption::VALUE_REQUIRED, 'add the one of these; composer, directories, cache or all', null, ['composer', 'directories', 'cache', 'all']);
        $this->addOption('create-directories');
        $this->addOption('create-cache');
        $this->addOption('create-dev', null, InputOption::VALUE_REQUIRED, 'add the one of these; composer, directories, all', null, ['composer', 'directories', 'cache', 'all']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if($input->getOption('create-dev')){
            $success = $this->clearDev($input, $output);
            return $success ? Command::SUCCESS : Command::FAILURE;
        }

        if($input->getOption('create-directories')){
            $success = $this->createDirectories($input, $output);
            return $success ? Command::SUCCESS : Command::FAILURE;
        }

        if($input->getOption('create-cache')){
            $success = $this->createCache($input, $output);
            return $success ? Command::SUCCESS : Command::FAILURE;
        }

        if($input->getOption('clear-dev')){
            $success = $this->clearDev($input, $output);
            return $success ? Command::SUCCESS : Command::FAILURE;
        }


        $output->writeln('<info>Add one of the options to do something: '.$this->getSynopsis().'</info>');
        return Command::FAILURE;
    }

    protected function createDirectories(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $this->setDevpath();
        $this->setGroups();

        $group = $this->getChoiceQuestion($helper, $input, $output, 'What is the group the file belongs to?', $this->groups, '<error>Choose a group</error>');

        if($group === false){
            return false;
        }

        $this->setConfig($group);

        if(!isset($this->config['directories']) || empty($this->config['directories'])) {
            $output->writeln('<error>There is no directory key found in the config file, or it is empty. Change the config before continuing with the directory creation.</error>');
            return false;
        }

        return $this->{strtolower($group) . 'Flow'}($input, $output, $helper);
    }

    protected function createDev(InputInterface $input, OutputInterface $output) : bool
    {
        $create = $input->getOption('create-dev');
        $rootpath = $this->getPath('', 1);

        if(in_array($create, ['composer', 'all'])) {
            $composerContent = $this->getComposerContent($rootpath);

            $composerContent['autoload']['psr-4'][] = [self::DEV_NAMESPACE => ''];

            $this->setComposerContent($rootpath, $composerContent);

            $output->writeln('<info>Created '.self::DEV_NAMESPACE.' in the psr-4 section of the composer file. Add the directory where the Codestarter development files will be stored.</info>');
        }

        if(in_array($create, ['directories', 'all'])) {
            $this->setDevpath();

            if($this->devPath === '') {
                $output->writeln('<error>The dev root path is not added or empty in the composer file. run --create-dev=composer first or add the directory to the Codestarter dev namespace.</error>');
                return false;
            }

            $this->fs->mirror($rootpath.'/assets/Flows', $this->devPath.'/Flows');
        }

        return true;
    }

    private function clearDev(InputInterface $input, OutputInterface $output): bool
    {
        $clear = $input->getOption('clear-dev');

        if(in_array($clear, ['all', 'composer'])) {
            $path = $this->getPath('', 1);
            $composerContent = $this->getComposerContent($path);

            unset($composerContent['autoload']['psr-4'][self::DEV_NAMESPACE]);

            $this->setComposerContent($path, $composerContent);
        }

        if(in_array($clear, ['all', 'directory'])) {
            $this->setDevpath();

            $this->fs->remove($this->devPath);
        }

        return true;
    }

    private function createCache(InputInterface $input, OutputInterface $output) : bool
    {
        $this->setDevpath();
        $this->setConfig('Php');

        if(!isset($this->config['class_cache'])){
            $output->writeln('<error>No class cache file in the config. Please add it with the class_cache key.</error>');
            return false;
        }

        if(!isset($this->config['root_namespace'])){
            $output->writeln('<error>No root namespace in the config. Please add it with the root_namepace key. The value is the namespace that stores the application files.</error>');
            return false;
        }

        $path = $this->getPath('', 1);
        $rootPath = $this->getPathFromComposerNamespace($path, $this->config['root_namespace']);

        $classes = Lister::classesInDirectories(
            $rootPath,
            dirname(__DIR__) . '/../vendor'
        );

        $output->writeln('<info>Classes are being collected.</info>');

        $content= '';

        foreach ($classes as $class) {
            $content .= $class."\n";
        }

        $output->writeln('<info>Classes are going to be added to cache.</info>');

        $this->fs->dumpFile($this->devPath.'/Flows/Php/'.$this->config['class_cache'], $content);

        $output->writeln('<info>Classes are added to cache.</info>');

        return true;
    }

    protected function phpFlow(InputInterface $input, OutputInterface $output, HelperInterface $helper): bool
    {
        $path = $this->getPath('', 1);
        $rootPath = $this->getPathFromComposerNamespace($path, $this->config['root_namespace']);

        $directories = $this->config['directories'];
        $deepestpaths = $this->getDeepestPaths($directories);
        $firstDirectory = strstr($deepestpaths[0], '/', true);
        $patterns = $this->getPatterns($deepestpaths);

        if(file_exists($rootPath.$firstDirectory) === false) {
            $confirmation = new ConfirmationQuestion('Do you want to create the file structure from the config?');
            if($helper->ask($input, $output, $confirmation)){
                return $this->createDirectoryStructure($rootPath, $deepestpaths, $patterns, $output, $input, $helper);
            }

            $output->writeln('<info>Creating of the file structure is canceled.</info>');
            return true;
        }

        $confirmation = new ConfirmationQuestion('Do you want to create an additional file structure?', false);
        if($helper->ask($input, $output, $confirmation)){
            return $this->createDirectoryStructure($rootPath, $deepestpaths, $patterns, $output, $input, $helper);
        }

        $output->writeln('<info>Creating of the file structure is canceled.</info>');
        return true;
    }

    protected function getDeepestPaths($structure, $currentPath = '') {
        $deepestPaths = [];

        foreach ($structure as $key => $value) {
            if (is_array($value)) {
                $deepestPaths = array_merge($deepestPaths, $this->getDeepestPaths($value, $currentPath ==='' ? $key : $currentPath . '/' . $key));
            } else {
                $deepestPaths[] = $currentPath === '' ? $value : $currentPath . '/' . $value;
            }
        }

        return $deepestPaths;
    }

    protected function getPatterns(array $deepestpaths) : array
    {
        $patterns = [];

        foreach ($deepestpaths as $path) {
            if(preg_match('/{\w+}/', $path, $matches)) {
                $patterns = array_unique(array_merge($patterns, $matches));
            }
        }

        return $patterns;
    }

    protected function createDirectoryStructure(string $rootPath, array $deepestpaths, array $patterns, OutputInterface $output, InputInterface $input, HelperInterface $helper) : bool
    {
        if(count($patterns) > 0) {
            $patternReplacements = [];

            foreach($patterns as $pattern) {
                $patternQuestion = new Question("What is the value for $pattern?");
                $patternAnswer = $helper->ask($input, $output, $patternQuestion);
                $patternReplacements[$pattern] = $patternAnswer;
            }

            $deepestpaths = array_map(function($path) use($patternReplacements) {
                return str_replace(array_keys($patternReplacements), $patternReplacements, $path);
            }, $deepestpaths);
        }

        $output->writeln('<info>Creating the file structure...</info>');

        foreach($deepestpaths as $path) {
            $this->fs->mkdir($rootPath.'/'.$path);
        }

        $output->writeln('<info>The file structure is created</info>');
        return true;
    }
}