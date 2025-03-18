<?php

use Xwero\Codestarter\Flows\Php\Content;
use Xwero\Codestarter\Flows\Php\DTO\AttributeDTO;
use Xwero\Codestarter\Flows\Php\DTO\CaseDTO;
use Xwero\Codestarter\Flows\Php\DTO\ImportDTO;
use Xwero\Codestarter\Flows\Php\DTO\ScalarOrClassDTO;
use Xwero\Codestarter\Flows\Php\Method;
use Xwero\Codestarter\Flows\Php\Type;
use Xwero\Codestarter\Flows\Php\TypedCollection\AttributeDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\CaseDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\ImportDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\MethodCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\ScalarOrClassDTOCollection;

covers(
    Content::class,
    ImportDTOCollection::class,
    MethodCollection::class,
    CaseDTO::class,
    CaseDTOCollection::class
);


describe('Content template class', function() {
    beforeEach(function () {
        $this->content = new Content();
    });

    afterEach(function () {
        unset($this->content);
    });

    test('displays imports output', function($result, $import) {
        $this->content->imports =  new ImportDTOCollection($import);

        expect($this->content->getImports())->toContain($result);
    })->with([
        'one import' => [
            'use Test;',
            [new ImportDTO('Test', Type::Object)]
        ],
        'two imports' => [
            "use Test;\nuse Test2;",
            [new ImportDTO('Test', Type::Object), new ImportDTO('Test2', Type::Object)]
        ],
    ]);

    test('faulty import', function() {
        $this->content->imports =  new ImportDTOCollection(['not-an-import']);
    })->throws(InvalidArgumentException::class);

    test('displays attributes output', function($result, $attributes) {
        $this->content->typeAttributes = new AttributeDTOCollection($attributes);

        expect($this->content->getTypeDefinition())->toContain($result);
    })->with([
        'one attribute' =>[
            '#[Test]',
            [new AttributeDTO('Test', 'Test')]
        ],
        'one attribute with argument' => [
            '#[Test("test")]',
            [new AttributeDTO('Test', 'Test', '"test"')]
        ],
        'two attributes' => [
            "#[Test]\n#[Test2]",
            [new AttributeDTO('Test', 'Test'), new AttributeDTO('Test2', 'Test')]
        ],
    ]);

   test('displays type output', function($result, $type) {
       $this->content->type = $type;

       expect($this->content->getTypeDefinition())->toContain($result);
   })->with([
       'class' =>['class', 'class'],
       'enum' => ['enum', 'enum'],
       'trait' => ['trait', 'trait'],
       'interface' => ['interface', 'interface'],
   ]);

   test('displays prefixes output', function($result, $prefixes) {
       $this->content->typePrefixes = $prefixes;

       expect($this->content->getTypeDefinition())->toContain($result);
   })->with([
       'one prefix' => ['static', ['static']],
       'two prefixes' => ['public static', ['public', 'static']],
   ]);

   test('displays extends output', function() {
       $this->content->typeExtend = new ScalarOrClassDTO('Test');

       expect($this->content->getTypeDefinition())->toContain('extends Test');
   });

    test('displays implements output', function($result, $implements) {
        $this->content->typeImplements = new ScalarOrClassDTOCollection($implements);

        expect($this->content->getTypeDefinition())->toContain($result);
    })->with([
        'one implement' => ['implements Test', [new ScalarOrClassDTO('Test')]],
        'two implements' => ["implements Test, Test2", [new ScalarOrClassDTO('Test'), new ScalarOrClassDTO('Test2')]],
    ]);

    test('displays method output', function($result, $method) {
       $this->content->methods = new MethodCollection([$method]);

        expect($this->content->getMethods())->toContain($result);
    })->with([
        'method definition' => ['function test()', new Method('test')],
    ]);

    test('display full class output', function() {
        $this->content->namespace = 'Xwero\\Codestarter\\Test';
        $this->content->typeName = 'Test';
        $this->content->type = 'class';

        $content = $this->content->getGeneratedContent();

        expect($content)->toContain('<?php')
            ->and($content)->not->toContain('use')
            ->and($content)->toContain('namespace Xwero\\Codestarter\\Test;')
            ->and($content)->toContain("class Test\n{");
    });

    test('display full enum output', function() {
        $this->content->typeName = 'Test';
        $this->content->type = 'enum';
        $this->content->typeReturns = new ScalarOrClassDTOCollection([new ScalarOrClassDTO('string')]);
        $this->content->cases = new CaseDTOCollection([new CaseDTO('Test', "'test'")]);

        $content = $this->content->getGeneratedContent();

        expect($content)->toContain('enum Test : string')
            ->and($content)->toContain("case Test = 'test';");
    });

});