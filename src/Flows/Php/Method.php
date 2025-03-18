<?php

namespace Xwero\Codestarter\Flows\Php;

use Xwero\Codestarter\Flows\Php\DTO\PropertyDTO;
use Xwero\Codestarter\Flows\Php\TypedCollection\AttributeDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\PropertyDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\ScalarOrClassDTOCollection;

class Method extends AbstractTemplate
{
    const INDENT = '    ';

    public function __construct(
        public string $name,
        public AttributeDTOCollection $attributes = new AttributeDTOCollection(),
        public array $prefixes = [],
        public PropertyDTOCollection $arguments = new PropertyDTOCollection(),
        public ScalarOrClassDTOCollection $returnTypes = new ScalarOrClassDTOCollection(),
        public string $content = '',
        public bool $isInterface = false,
    )
    {}

    public function getDefinition(): string
    {
        $definition = '';

        if(count($this->attributes) > 0) {
            $definition .=  $this->getAttributesOutput($this->attributes);
        }

        if(count($this->prefixes) > 0) {
            $definition .= join(' ', $this->prefixes);
        }

        $definition .= ' function '.$this->name.'('.$this->getArguments().")";

        if(count($this->returnTypes) > 0) {
            $definition .= ' : '. join('|', array_map(function($type) {
                    return $type->type;
                }, $this->returnTypes->toArray()));
        }

        if($this->isInterface) {
            $definition .= ';';
        }

        return $definition."\n";
    }

    public function getContent(): string
    {
        if($this->isInterface) {
            return '';
        }

        $content = self::INDENT.'{';

        if(strlen($this->content) > 0) {
            $lines = explode(PHP_EOL, $this->content);
            $indentedLines = array_map(function($line) {
                return self::INDENT.self::INDENT.$line;
            }, $lines);
            $content .= "\n".implode(PHP_EOL, $indentedLines);
        }

        $content .= "\n".self::INDENT.'}';

        return $content;
    }

    private function getArguments() : string
    {
        $arguments = [];
        /** @var PropertyDTO $argument */
        foreach($this->arguments as $argument) {
             $temp = [];

             if(count($argument->prefixes) > 0) {
                 $temp[] = join(' ', $argument->prefixes);
             }

             if(count($argument->types) > 0) {
                 $temp[] = join('|', array_map(function($type) {
                     return $type->type;
                 }, $argument->types->toArray()));
             }

             $temp[] = '$'. $argument->name;

             if($argument->default !== null) {
                 $temp[] = '= '.$argument->default;
             }

             $arguments[] = join(' ',$temp);
        }

        return join(', ',$arguments);
    }
}