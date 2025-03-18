<?php

namespace Xwero\Codestarter\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Xwero\Codestarter\Devable;
use Xwero\Codestarter\DTO\GenerateDTO;
use Xwero\Codestarter\Flows\AbstractFlow;
use Xwero\Codestarter\Questionable;

#[AsCommand(name: 'make-file')]
class CodeStarterCommand extends AbstractCommand
{
    use Questionable;
    use Devable;
    public function __construct(Filesystem $fs)
    {
        parent::__construct($fs);

        $this->setDevPath();
        $this->setGroups();
    }

    protected function configure(): void
    {
        $this->addOption('to-cli');
        // hack to pass unknown options to the flows
        $this->addArgument('other', InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $group = $this->getChoiceQuestion(
            $helper,
            $input,
            $output,
            'What is the group the file belongs to?',
            $this->groups,
            '<error>Select a group. Or add one.</error>'
        );

        if($group === false) {
            return Command::FAILURE;
        }

        $flows = $this->getGroupFlows($group, $this->fs, $input, $output, $helper);

        $type = $this->getChoiceQuestion(
            $helper,
            $input,
            $output,
            'Which file type do you want to create?',
            array_keys($flows),
            '<error>Select a type.</error>',
        );

        if($type === false) {
            return Command::FAILURE;
        }

        /** @var AbstractFlow $class */
        $class = new $flows[$type]($this->fs, $input, $output, $helper);

        $checkResult = $class->configChecks($output);

        if($checkResult === false){
            return Command::FAILURE;
        }

        try {
            $fileNameQuestion = new Question('What is the name of the file?'."\n", '');
            $fileNameAnswer = $helper->ask($input, $output, $fileNameQuestion);

            if($fileNameAnswer === '') {
                $output->writeln('<error>Add the name of the file.</error>');
                return Command::FAILURE;
            }

            $extraOptions = array_diff(array_slice($GLOBALS['argv'], array_search('--', $GLOBALS['argv'])+1), ['--to-cli']);
            $newFile = $class->generate(new GenerateDTO($type, $fileNameAnswer, $extraOptions));

            if($input->getOption('to-cli')) {
                // Output the path first because it is annoying to scroll up to get the path.
                $output->writeln($newFile->content."\n".$newFile->path);

                return Command::SUCCESS;
            }

            $this->fs->dumpFile($newFile->path, $newFile->content);

            $output->writeln("<info>Created new file $newFile->path.</info>");

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }

    private function getGroupFlows(string $group, Filesystem $filesystem, InputInterface $input, OutputInterface $output, HelperInterface $helper) : array
    {
        $typesAndClasses = [];

        $classes = [
            "Xwero\Codestarter\Flows\\$group\TypeFlow"
        ];

        foreach ($this->getGroupCustomFlowClasses($group) as $class) {
            $classes[] = $class;
        }
        // This makes it possible to override the default type flows
        foreach ($classes as $class) {
            $instance = new $class($filesystem, $input, $output, $helper);
            $types = $instance->types;

            foreach ($types as $type) {
                $typesAndClasses[$type] = $class;
            }
        }

        return $typesAndClasses;
    }

    protected function getGroupCustomFlowClasses(string $group): array
    {
        $classes = [];

        if (file_exists($this->devPath . "/Flows/$group/TypeFlow.php")) {
            $classes[] =  "Xwero\Codestarter\Flows\\$group\TypeFlow";
        }

        foreach(glob($this->devPath . "/Flows/$group/*/TypeFlow.php") as $file) {
            $parent = str_replace(dirname($file, 2), "", dirname($file));
            $classes[] =  "Xwero\Codestarter\Flows\\$group\\$parent\TypeFlow";
        }

        return $classes;
    }
}