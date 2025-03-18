<?php

namespace Xwero\Codestarter\Flows\Php;


use Xwero\Codestarter\Flows\Php\DTO\ScalarOrClassDTO;
use Xwero\Codestarter\Flows\Php\TypedCollection\AttributeDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\CaseDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\ImportDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\MethodCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\ScalarOrClassDTOCollection;

class Content extends AbstractTemplate
{

    public function __construct(
        public string       $namespace = '',
        public ImportDTOCollection        $imports = new ImportDTOCollection(),
        public string       $type = '',
        public AttributeDTOCollection        $typeAttributes = new AttributeDTOCollection(),
        public array        $typePrefixes = [],
        public string       $typeName = '',
        public ScalarOrClassDTO       $typeExtend = new ScalarOrClassDTO(''),
        public ScalarOrClassDTOCollection        $typeImplements = new ScalarOrClassDTOCollection(),
        public ScalarOrClassDTOCollection        $typeReturns = new ScalarOrClassDTOCollection(),
        public MethodCollection        $methods = new MethodCollection(),
        public CaseDTOCollection        $cases = new CaseDTOCollection(),
    )
    {}

    public function getImports(): string
    {
        $useLines = '';

        if(count($this->imports) > 0) {
            foreach($this->imports as $import) {
                $useLines .= 'use '.$import->path.";\n";
            }
        }

        return $useLines;
    }
    public function getTypeDefinition(): string
    {
        $definition = '';

        if(count($this->typeAttributes) > 0) {
            $definition .= $this->getAttributesOutput($this->typeAttributes);
        }

        if(count($this->typePrefixes) > 0) {
            $definition .= join(' ', $this->typePrefixes);
        }

        $definition .= ' '.$this->type.' '.$this->typeName;

        if($this->typeExtend->type !== '') {
            $definition .= ' extends '.$this->typeExtend->type;
        }

        if(count($this->typeImplements) > 0) {
            $definition .= ' implements '.join(', ', array_map(function($type) {
                    return $type->type;
                }, $this->typeImplements->toArray()));;
        }

        if(count($this->typeReturns) > 0) {
            $definition .= ' : '.join('|', array_map(function($type) {
                    return $type->type;
                }, $this->typeReturns->toArray()));
        }

        $definition .= "\n";

        return $definition;
    }

    public function getMethods(): string
    {
        $methods = '';

        if(count($this->methods) > 0) {
            $template = __DIR__ . '/templates/method.php';
            foreach($this->methods as $method) {
                $methods .= $this->getCodeFromTemplate($template, $method);
            }
        }

        return $methods;
    }

    public function getCases(): string
    {
        $cases = '';

        if(count($this->cases) > 0) {
            foreach($this->cases as $case) {
                $cases .= 'case '.$case->name;

                if($case->value !== '') {
                    $cases .= ' = '.$case->value;
                }

                $cases .= ';';
            }
        }

        return $cases;
    }

    public function getGeneratedContent(): string
    {
        $template = __DIR__ . '/templates/type.php';
        return $this->getCodeFromTemplate($template, $this);
    }
}