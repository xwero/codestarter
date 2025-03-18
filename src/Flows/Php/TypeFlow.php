<?php

namespace Xwero\Codestarter\Flows\Php;

use Xwero\Codestarter\Flows\Php\DTO\CaseDTO;
use Xwero\Codestarter\Flows\Php\DTO\ImportDTO;
use Xwero\Codestarter\DTO\GenerateDTO;
use Xwero\Codestarter\DTO\NewFileDTO;
use Xwero\Codestarter\Flows\Php\DTO\ScalarOrClassDTO;
use Xwero\Codestarter\Flows\Php\TypedCollection\CaseDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\ImportDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\MethodCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\ScalarOrClassDTOCollection;

class TypeFlow extends AbstractPhpFlow
{
    public array $types = ['class', 'interface', 'trait', 'enum'];

    public function generate(GenerateDTO $props): NewFileDTO
    {
        $content = new Content();
        $content->type = $props->type;
        $content->typeName = $props->filename;
        $content->namespace = $this->getAutoCompleteQuestion($this->helper, $this->input, $this->output,'What is the namespace?', $this->getNamespaces());

        $this->{$props->type . 'Flow'}($content, $props->type);

        $path = str_replace([$this->config['root_namespace'], '\\'], [$this->rootPath, '/'], $content->namespace).'/'.$content->typeName.'.php';

        return new NewFileDTO($path, $content->getGeneratedContent());
    }

    private function classFlow(Content &$content, string $type) : void {
        if($this->getConfirmationFromQuestion('Has the class attributes?')) {
            $attributes = $this->getAttributesFromQuestion(
                'What is the attribute class?',
                'Does the attribute have arguments?',
                'What are the arguments?',
            );

            $attributeImports = new ImportDTOCollection();

            foreach($attributes as $attribute) {
                if($attribute->import !== '') {
                    $attributeImports[] = new ImportDTO($attribute->import, Type::Object);
                }
            }

            $content->imports = $content->imports->append($attributeImports);

            $content->typeAttributes = $attributes;
        }

        if($this->getConfirmationFromQuestion('Is the class abstract?')) {
            $content->typePrefixes[] = 'abstract';
        }

        if($this->getConfirmationFromQuestion('Is the class final?')) {
            $content->typePrefixes[] = 'final';
        }

        if($this->getConfirmationFromQuestion('Is the class readonly?')) {
            $content->typePrefixes[] = 'readonly';
        }

        if($this->getConfirmationFromQuestion('Has the class a parent?')) {
            $extendAnswer = $this->getAutoCompleteQuestion($this->helper, $this->input, $this->output, 'What is the parent type?', $this->getClassesFromCache());
            $content->imports[] = new ImportDTO($extendAnswer, Type::Object);
            $content->typeExtend = new ScalarOrClassDTO($this->getClassFromClasspath($extendAnswer));
        }

        if($this->getConfirmationFromQuestion('Has the class interfaces?')) {
            $interfacesAnswer = $this->getClassesFromQuestion('What is the interface type?');
            $interfaces = new ImportDTOCollection(array_map(fn($import) => new ImportDTO($import, Type::Interface), array_keys($interfacesAnswer)));

            $content->imports = $content->imports->append($interfaces);
            $content->typeImplements = new ScalarOrClassDTOCollection(array_values($interfacesAnswer));
        }

        $this->runMethodsFlow($content, $type);
    }

    private function interfaceFlow(Content &$content, string $type) : void {
        if($this->getConfirmationFromQuestion('Has the interface attributes?')) {
            $attributes = $this->getAttributesFromQuestion(
                'What is the attribute class?',
                'Does the attribute have arguments?',
                'What are the arguments?',
            );

            $attributeImports = new ImportDTOCollection();

            foreach($attributes as $attribute) {
                if($attribute->import !== '') {
                    $attributeImports[] = new ImportDTO($attribute->import, Type::Object);
                }
            }

            $content->imports = $content->imports->append($attributeImports);

            $content->typeAttributes = $attributes;
        }

        if($this->getConfirmationFromQuestion('Has the interface a parent?')) {
            $extendAnswer = $this->getAutoCompleteQuestion($this->helper, $this->input, $this->output, 'What is the parent type?', $this->getClassesFromCache());
            $content->imports[] = new ImportDTO($extendAnswer, Type::Object);
            $content->typeExtend = new ScalarOrClassDTO($this->getClassFromClasspath($extendAnswer));
        }

        if($this->getConfirmationFromQuestion('Has the interface interfaces?')) {
            $interfacesAnswer = $this->getClassesFromQuestion('What is the interface type?');
            $interfaces = new ImportDTOCollection(array_map(fn($import) => new ImportDTO($import, Type::Interface), array_keys($interfacesAnswer)));

            $content->imports = $content->imports->append($interfaces);
            $content->typeImplements = new ScalarOrClassDTOCollection(array_values($interfacesAnswer));
        }

        $this->runMethodsFlow($content, $type);
    }

    private function traitFlow(Content &$content, string $type) : void {
        $this->runMethodsFlow($content, $type);
    }

    private function enumFlow(Content &$content, string $type) : void {
        $backedEnum  = false;

        if($this->getConfirmationFromQuestion('Is it a backed enum?')) {
            $backedEnum = true;
            $backedReturn = $this->getSinglelineFromQuestion('What is the return scalar?');
            $content->typeReturns =  new ScalarOrClassDTOCollection([New ScalarOrClassDTO($backedReturn)]);
        }

        $cases = new CaseDTOCollection();

        while(true) {
            $caseAnswer = $this->getSinglelineFromQuestion('What is the case?');

            if($caseAnswer === '') {
                break;
            }

            $caseValueAnswer = $backedEnum ? $this->getSinglelineFromQuestion('What is the case value?') : '';

            $cases[] = new CaseDTO($caseAnswer, $caseValueAnswer);
        }

        $content->cases = $cases;

        $this->runMethodsFlow($content, $type);
    }

    private function runMethodsFlow(Content &$content, string $type) : void {
        if($this->getConfirmationFromQuestion("Has the $type methods?")) {
            $methodsAnswer = $this->getMethodsFromQuestion(
                $type,
                'What is the method name?',
                'Has the method attributes?',
                'What is the atribute class?',
                'Has the attribute arguements?',
                'What are the attribute arguments?',
                'What is the method visibility?',
                'Is the method static?',
                'has the method arguments?',
                'What is the argument visibility?',
                'Is the argument readonly?',
                'Has the argument a  type?',
                'Is it a single type?',
                'Is the type a scalar?',
                'What is the scalar?',
                'What class is the type?',
                'What is the name of the argument?',
                'What is the default of the argument?',
                'Do you want to add a return type?',
                'Is it a single type?',
                'Is it a scalar?',
                'Fill in the scalar',
                'Select the type',
                'Do you want to add the body of the method?',
                'What is the body?',
            );

            foreach($methodsAnswer as $method) {
                foreach($method->arguments as $argument) {
                    foreach($argument->types as $type) {
                        if ($type->import !== '') {
                            $content->imports[] = new ImportDTO($type->import, Type::Object);
                        }
                    }
                }

                foreach($method->returnTypes as $returnType) {
                    if($returnType->import !== '') {
                        $content->imports[] = new ImportDTO($returnType->import, Type::Object);
                    }
                }
            }

            if($type === 'interface') {
                $methodsAnswer = new MethodCollection(array_map(function($method) {
                    $method->isInterface = true;
                    return $method;
                }, $methodsAnswer->toArray()));
            }

            $content->methods = $methodsAnswer;
        }

        // TODO: add question to create import files that don't exist.
    }

    private function getMethodFromQuestion(
        string $type,
        string $nameQuestion,
        string $attributesConfirmationQuestion,
        string $attributeClassQuestion,
        string $attributeArgumentConfirmationQuestion,
        string $attributeArgumentValueQuestion,
        string $visibilityQuestion,
        string $staticConfirmationQuestion,
        string $argumentConfirmationQuestion,
        string $argumentVisibilityQuestion,
        string $argumentReadonlyQuestion,
        string $argumentTypeConfirmationQuestion,
        string $argumentTypeSingleConfirmationQuestion,
        string $argumentTypeScalarConfirmationQuestion,
        string $argumentTypeScalarQuestion,
        string $argumentTypeClassQuestion,
        string $argumentNameQuestion,
        string $argumentDefaultQuestion,
        string $returnTypeConfirmationQuestion,
        string $returnTypeSingleConfirmationQuestion,
        string $returnTypeScalarConfirmationQuestion,
        string $returnTypeScalarQuestion,
        string $returnTypeClassQuestion,
        string $bodyConfirmationQuestion,
        string $bodyQuestion,
    ) : Method
    {
        $nameAnswer = $this->getSinglelineFromQuestion($nameQuestion);

        if($nameAnswer === '') {
            return new Method('');
        }

        $method = new Method($nameAnswer);

        if($this->getConfirmationFromQuestion($attributesConfirmationQuestion)) {
            $method->attributes = $this->getAttributesFromQuestion(
                $attributeClassQuestion,
                $attributeArgumentConfirmationQuestion,
                $attributeArgumentValueQuestion,
            );
        }

        $visibilityAnswer = $this->getSinglelineFromQuestion($visibilityQuestion);

        if($visibilityAnswer) {
            $method->prefixes[] = $visibilityAnswer;
        }

        if($this->getConfirmationFromQuestion($staticConfirmationQuestion)) {
            $method->prefixes[] = 'static';
        }

        if($this->getConfirmationFromQuestion($argumentConfirmationQuestion)) {
            $argumentsAnswer = $this->getPropertiesFromQuestion(
                $argumentVisibilityQuestion,
                $argumentReadonlyQuestion,
                $argumentTypeConfirmationQuestion,
                $argumentTypeSingleConfirmationQuestion,
                $argumentTypeScalarConfirmationQuestion,
                $argumentTypeScalarQuestion,
                $argumentTypeClassQuestion,
                $argumentNameQuestion,
                $argumentDefaultQuestion,
            );

            $method->arguments = $argumentsAnswer;
        }

        if($this->getConfirmationFromQuestion($returnTypeConfirmationQuestion)) {
            $method->returnTypes = $this->getScalarsClassesFromQuestion(
                $returnTypeSingleConfirmationQuestion,
                $returnTypeScalarConfirmationQuestion,
                $returnTypeScalarQuestion,
                $returnTypeClassQuestion,
            );
        }

        if($type !== 'interface' &&  $this->getConfirmationFromQuestion($bodyConfirmationQuestion)) {
            $method->content = $this->getMultilineFromQuestion($bodyQuestion);
        }

        return $method;
    }

    private function getMethodsFromQuestion(
        string $type,
        string $nameQuestion,
        string $attributesConfirmationQuestion,
        string $attributeClassQuestion,
        string $attributeArgumentConfirmationQuestion,
        string $attributeArgumentValueQuestion,
        string $visibilityQuestion,
        string $staticConfirmationQuestion,
        string $argumentConfirmationQuestion,
        string $argumentVisibilityQuestion,
        string $argumentReadonlyQuestion,
        string $argumentTypeConfirmationQuestion,
        string $argumentTypeSingleConfirmationQuestion,
        string $argumentTypeScalarConfirmationQuestion,
        string $argumentTypeScalarQuestion,
        string $argumentTypeClassQuestion,
        string $argumentNameQuestion,
        string $argumentDefaultQuestion,
        string $returnTypesConfirmationQuestion,
        string $returnTypeSingleConfirmationQuestion,
        string $returnTypeScalarConfirmationQuestion,
        string $returnTypeScalarQuestion,
        string $returnTypeClassQuestion,
        string $bodyConfirmationQuestion,
        string $bodyQuestion,
    ) : MethodCollection
    {
        $methods = new MethodCollection();

        while(true) {
            $method = $this->getMethodFromQuestion(
                $type,
                $nameQuestion,
                $attributesConfirmationQuestion,
                $attributeClassQuestion,
                $attributeArgumentConfirmationQuestion,
                $attributeArgumentValueQuestion,
                $visibilityQuestion,
                $staticConfirmationQuestion,
                $argumentConfirmationQuestion,
                $argumentVisibilityQuestion,
                $argumentReadonlyQuestion,
                $argumentTypeConfirmationQuestion,
                $argumentTypeSingleConfirmationQuestion,
                $argumentTypeScalarConfirmationQuestion,
                $argumentTypeScalarQuestion,
                $argumentTypeClassQuestion,
                $argumentNameQuestion,
                $argumentDefaultQuestion,
                $returnTypesConfirmationQuestion,
                $returnTypeSingleConfirmationQuestion,
                $returnTypeScalarConfirmationQuestion,
                $returnTypeScalarQuestion,
                $returnTypeClassQuestion,
                $bodyConfirmationQuestion,
                $bodyQuestion,
            );

            if($method->name === '') {
                break;
            }

            $methods[] = $method;
        }

        return $methods;
    }
}