<?php

namespace Xwero\Codestarter\Flows;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Xwero\Codestarter\Composerable;
use Xwero\Codestarter\Devable;
use Xwero\Codestarter\DTO\GenerateDTO;
use Xwero\Codestarter\DTO\NewFileDTO;
use Xwero\Codestarter\Pathable;
use Xwero\Codestarter\Questionable;

class AbstractFlow
{
    use Devable;
    use Questionable;

    public string $group = '';
    /**
     * Add the types that can be selected when running the codestarter command.
     * The types class, trait, interface and enum are added  by default.
     * The types can be overridden by adding a type with the same name to a custom flow.
     *
     * @var array
     */
    public array $types = [];

    public function __construct(
        protected Filesystem $filesystem,
        protected InputInterface $input,
        protected OutputInterface $output,
        protected HelperInterface $helper
    )
    {
        $this->setDevpath();
    }

    /**
     * Override this method in the custom flow class
     *
     * @param string $type
     * @param string $fileName
     * @return NewFileDTO
     */
    public function generate(GenerateDTO $props): NewFileDTO {
        $this->output->writeln('<error>The flow function is not implemented.</error>');
        return new NewFileDTO('','');
    }

    public function configChecks(OutputInterface $output): bool
    {
        return true;
    }

    protected function getDeepestDirectories($parentDir) {
        $deepestDirs = [];
        $maxDepth = 0;

        // Recursively traverse directories using RecursiveIteratorIterator
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($parentDir),
            RecursiveIteratorIterator::SELF_FIRST
        );

        // Loop through all directories
        foreach ($iterator as $file) {
            // Check if it's a directory and not the root directory
            if ($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {
                // Calculate depth of the current directory
                $depth = $iterator->getDepth();

                // Update max depth and reset the deepestDirs array if a new max depth is found
                if ($depth > $maxDepth) {
                    $maxDepth = $depth;
                    $deepestDirs = [$file->getPathname()];
                }
                // If the current directory is at the same max depth, add it to the list
                elseif ($depth == $maxDepth) {
                    $deepestDirs[] = $file->getPathname();
                }
            }
        }

        return $deepestDirs;
    }

    /**
     * Helper to make it easier to work with a yes-no question
     *
     * @param string $question
     * @return bool
     */
    protected function getConfirmationFromQuestion(string $question) : bool
    {
        $confirmationQuestion = new ConfirmationQuestion($question." [yN]\n", false);
        return $this->helper->ask($this->input, $this->output, $confirmationQuestion);
    }

    /**
     * A helper method to get the text input of a question
     *
     * @return string
     */
    protected function getSinglelineFromQuestion(string $question): string
    {
        $methodBodyQuestion = new Question($question."\n");

        $answer = $this->helper->ask($this->input, $this->output, $methodBodyQuestion);

        return $answer ?? '';
    }

    /**
     * A helper method to get the input of a multiline question
     *
     * @return string
     */
    protected function getMultilineFromQuestion(string $question): string
    {
        $methodBodyQuestion = new Question($question." (Use ctrl-z or ctrl-d to finish the content input)\n");
        $methodBodyQuestion->setMultiline(true);

        $answer = $this->helper->ask($this->input, $this->output, $methodBodyQuestion);

        return $answer ?? '';
    }

    protected function getChoiceFromQuestion(string $question, array $choices, string $errorMessage, array $multiSelectDefaults = []): array|string
    {
        return $this->getChoiceQuestion($this->helper, $this->input, $this->output, $question, $choices, $errorMessage, $multiSelectDefaults);
    }
}