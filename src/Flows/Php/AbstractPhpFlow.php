<?php

namespace Xwero\Codestarter\Flows\Php;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Xwero\Codestarter\Flows\AbstractFlow;
use Xwero\Codestarter\Flows\Php\DTO\AttributeDTO;
use Xwero\Codestarter\Flows\Php\DTO\PropertyDTO;
use Xwero\Codestarter\Flows\Php\DTO\ScalarOrClassDTO;
use Xwero\Codestarter\Flows\Php\TypedCollection\AttributeDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\PropertyDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\ScalarOrClassDTOCollection;

abstract class AbstractPhpFlow extends AbstractFlow
{
    protected string $classCache = '';

    public function __construct(Filesystem $filesystem, InputInterface $input, OutputInterface $output, HelperInterface $helper)
    {
        parent::__construct($filesystem, $input, $output, $helper);

        $this->setConfig('Php');

        if($this->hasRootNamespace()) {
            $this->setRootPath();
        }
    }

    /**
     * A helper method to get the namespace (WIP)
     *
     * @param string $question
     * @return string
     */
    protected function getNamespaces(): array {
        $rootPath = $this->rootPath. array_key_first($this->config['directories']);
        $directories = $this->getDeepestDirectories($this->rootPath);
        $namespaces = [];

        foreach($directories as $directory) {
            $namespaces[] = $this->config['root_namespace'].str_replace([$this->rootPath, '/'], ['', '\\'], $directory);
        }

        return $namespaces;
    }

    /**
     * Question flow for an attribute
     *
     * @param string $classQuestion
     * @param string $argumentConfirmationQuestion
     * @param string $argumentValueQuestion
     * @return AttributeDTO
     */
    protected function getAttributeFromQuestion(
        string $classQuestion,
        string $argumentConfirmationQuestion,
        string $argumentValueQuestion,
    ): AttributeDTO {
        $classAnswer = $this->getAutoCompleteQuestion($this->helper, $this->input, $this->output, $classQuestion, $this->getClassesFromCache());

        if($classAnswer === '') {
            return new AttributeDTO('', '', '');
        }

        $confirmationAnswer = $this->getConfirmationFromQuestion($argumentConfirmationQuestion);
        $attribute = $this->getClassFromClasspath($classAnswer);

        if($confirmationAnswer === false) {
            return new AttributeDTO($attribute, $classAnswer);
        }

        $valueAnswer = $this->getSinglelineFromQuestion($argumentValueQuestion);

        return new AttributeDTO($attribute, $classAnswer, $valueAnswer);
    }

    /**
     * Repeating the attribute flow to get all the wanted attributes
     *
     * @param string $classQuestion
     * @param string $argumentConfirmationQuestion
     * @param string $argumentValueQuestion
     * @return array
     */
    protected function getAttributesFromQuestion(
        string $classQuestion,
        string $argumentConfirmationQuestion,
        string $argumentValueQuestion,
    ) : AttributeDTOCollection {
        $attributes = new AttributeDTOCollection();

        while(true) {
            $attribute = $this->getAttributeFromQuestion($classQuestion, $argumentConfirmationQuestion, $argumentValueQuestion);

            if($attribute->class === '') {
                break;
            }

            $attributes[] = $attribute;
        }

        return $attributes;
    }

    /**
     * The question flow to build a property/argument
     *
     * @param string $visibilityQuestion
     * @param string $readonlyQuestion
     * @param string $typeConfirmationQuestion
     * @param string $typeSingleConfirmationQuestion
     * @param string $typeScalarConfirmationQuestion
     * @param string $typeScalarQuestion
     * @param string $typeClassQuestion
     * @param string $nameQuestion
     * @param string $defaultQuestion
     * @return PropertyDTO
     */
    protected function getPropertyFromQuestion(
        string $visibilityQuestion,
        string $readonlyQuestion,
        string $typeConfirmationQuestion,
        string $typeSingleConfirmationQuestion,
        string $typeScalarConfirmationQuestion,
        string $typeScalarQuestion,
        string $typeClassQuestion,
        string $nameQuestion,
        string $defaultQuestion,
    ) : PropertyDTO {
        $nameAnswer = $this->getSinglelineFromQuestion($nameQuestion);

        if($nameAnswer === '') {
            return new PropertyDTO('');
        }

        $property = new PropertyDTO($nameAnswer);

        $visibilityAnswer = $this->getVisibilityFromQuestion($visibilityQuestion);

        if(strlen($visibilityAnswer) > 0) {
            $property->prefixes[] = $visibilityAnswer;
        }

        if($this->getConfirmationFromQuestion($readonlyQuestion)) {
            $property->prefixes[] = 'readonly';
        }

        if($this->getConfirmationFromQuestion($typeConfirmationQuestion)) {
            $typesAnswer = $this->getScalarsClassesFromQuestion(
                $typeSingleConfirmationQuestion,
                $typeScalarConfirmationQuestion,
                $typeScalarQuestion,
                $typeClassQuestion,
            );

            $property->types = $typesAnswer;
        }

        $property->default = $this->getSinglelineFromQuestion($defaultQuestion);

        return $property;
    }

    /**
     * Repeating the property flow to get the desired properties
     *
     * @param string $visibilityQuestion
     * @param string $readonlyQuestion
     * @param string $typeConfirmationQuestion
     * @param string $typeSingleConfirmationQuestion
     * @param string $typeScalarConfirmationQuestion
     * @param string $typeScalarQuestion
     * @param string $typeClassQuestion
     * @param string $nameQuestion
     * @param string $defaultQuestion
     * @return array
     */
    protected function getPropertiesFromQuestion(
        string $visibilityQuestion,
        string $readonlyQuestion,
        string $typeConfirmationQuestion,
        string $typeSingleConfirmationQuestion,
        string $typeScalarConfirmationQuestion,
        string $typeScalarQuestion,
        string $typeClassQuestion,
        string $nameQuestion,
        string $defaultQuestion,
    ) : PropertyDTOCollection {
        $properties = new PropertyDTOCollection();

        while(true) {
            $property = $this->getPropertyFromQuestion(
                $visibilityQuestion,
                $readonlyQuestion,
                $typeConfirmationQuestion,
                $typeSingleConfirmationQuestion,
                $typeScalarConfirmationQuestion,
                $typeScalarQuestion,
                $typeClassQuestion,
                $nameQuestion,
                $defaultQuestion,
            );

            if($property->name === '') {
                break;
            }

            $properties[] = $property;
        }

        return $properties;
    }

    /**
     * A helper method to get the type hinting
     *
     * @param string $singleConfirmationQuestion
     * @param string $scalarConfirmationQuestion
     * @param string $scalarQuestion
     * @param string $classQuestion
     * @return \Xwero\Codestarter\TypedCollection
     */
    public function getScalarsClassesFromQuestion(
        string $singleConfirmationQuestion,
        string $scalarConfirmationQuestion,
        string $scalarQuestion,
        string $classQuestion,
    ) : ScalarOrClassDTOCollection
    {
        if($this->getConfirmationFromQuestion($singleConfirmationQuestion)) {
            if($this->getConfirmationFromQuestion($scalarConfirmationQuestion)) {
                $scalarAnswer = $this->getSinglelineFromQuestion($scalarQuestion);
                return new ScalarOrClassDTOCollection([new ScalarOrClassDTO($scalarAnswer)]);
            } else {
                $classAnswer = $this->getAutoCompleteQuestion($this->helper, $this->input, $this->output, $classQuestion, $this->getClassesFromCache());;
                return new ScalarOrClassDTOCollection([new ScalarOrClassDTO($this->getClassFromClasspath($classAnswer), $classAnswer)]);
            }
        }
        // multiple types
        $types = new ScalarOrClassDTOCollection();

        while(true)
        {
            if($this->getConfirmationFromQuestion($scalarConfirmationQuestion)) {
                $scalarAnswer = $this->getSinglelineFromQuestion($scalarQuestion);

                if($scalarAnswer === '') {
                    break;
                }

                $types[] = new ScalarOrClassDTO($scalarAnswer);
            } else {
                $classAnswer = $this->getAutoCompleteQuestion($this->helper, $this->input, $this->output, $classQuestion, $this->getClassesFromCache());;

                if($classAnswer === '') {
                    break;
                }

                $types[] = new ScalarOrClassDTO($this->getClassFromClasspath($classAnswer), $classAnswer);
            }
        }

        return $types;
    }

    protected function getClassesFromCache(): array
    {
        $content = $this->filesystem->readFile($this->devPath.'/Flows/Php/'.$this->config['class_cache']);
        return explode("\n", $content);
    }

    /**
     * Repeating the class flow to get all the desired classes
     *
     * @param string $confirmQuestion
     * @param string $classQuestion
     * @return array
     */
    protected function getClassesFromQuestion(string $classQuestion) : array
    {
        $classes = [];

        while (true) {
            $classPath = $this->getAutoCompleteQuestion($this->helper, $this->input, $this->output, $classQuestion, $this->getClassesFromCache());

            if ($classPath === '') {
                break;
            }

            $classes[$classPath] = new ScalarOrClassDTO($this->getClassFromClasspath($classPath), $classPath);
        }

        return $classes;
    }

    /**
     * Select the visibility of classes and promoted properties
     *
     * @param string $question
     * @return string
     */
    protected function getVisibilityFromQuestion(string $question) : string
    {
        $vQuestion = new ChoiceQuestion($question."\n", Visibility::cases());
        $answer = $this->helper->ask($this->input, $this->output, $vQuestion);

        return $answer ?? '';
    }

    /**
     * Used for making the type hinting easier to read
     *
     * @param string $classpath
     * @return string
     */
    protected function getClassFromClasspath(string $classpath): string
    {
        $possibleFrontSlash = substr($classpath, 1);

        return str_contains($possibleFrontSlash, '\\') ?
            substr($classpath,  strrpos($classpath, '\\') + 1) :
            (str_starts_with($classpath, '\\') ? $possibleFrontSlash : $classpath );
    }

    protected function hasClassCache(): bool
    {
        $path = $this->devPath. '/Flows/Php' . $this->classCache;
        return $this->filesystem->exists($path);
    }

    protected function setRootPath(): void
    {
        $path = $this->getPath('', 1);

        $this->rootPath = $this->getPathFromComposerNamespace($path, $this->config['root_namespace']);
    }

    protected function hasRootNamespace(): bool
    {
        return isset($this->config['root_namespace']) && strlen($this->config['root_namespace']) > 0;
    }

    public function configChecks(OutputInterface $output): bool
    {
        if(!$this->hasClassCache()){
            $output->writeln("<error>The class cache file does not exist. Run `bin/codestarter generate-classes-cache` first.</error>");
            return false;
        }

        if(!$this->hasRootNamespace()){
            $output->writeln("<error>Create a Flows/Php/config.php file with an array that has the key root_namespace and add the project structure namespace.</error>");
            return false;
        }

        return true;
    }
}