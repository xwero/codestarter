<?php

use Xwero\Codestarter\Flows\Php\DTO\AttributeDTO;
use Xwero\Codestarter\Flows\Php\DTO\PropertyDTO;
use Xwero\Codestarter\Flows\Php\DTO\ScalarOrClassDTO;
use Xwero\Codestarter\Flows\Php\Method;
use Xwero\Codestarter\Flows\Php\TypedCollection\AttributeDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\PropertyDTOCollection;
use Xwero\Codestarter\Flows\Php\TypedCollection\ScalarOrClassDTOCollection;
use Xwero\Codestarter\TypedCollection;

covers(
    Method::class,
    AttributeDTO::class,
    ScalarOrClassDTO::class,
    PropertyDTO::class,
    ScalarOrClassDTOCollection::class,
    TypedCollection::class,
    PropertyDTOCollection::class,
    AttributeDTOCollection::class
);

describe('Method template class', function() {
    test('basic method', function() {
       $method = new Method('name');

       expect($method->getDefinition())->toContain('function name()');
    });

    test('displays attributes output', function($result, $methodClosure) {
        $method = $methodClosure();

        expect($method->getDefinition())->toContain($result);
    })->with([
        'one attribute' =>[
            '#[Test]',
            fn() => new Method('name', new AttributeDTOCollection([new AttributeDTO('Test', 'Test')])),
        ],
        'one attribute with argument' => [
            '#[Test("test")]',
            fn() => new Method('test', new AttributeDTOCollection([new AttributeDTO('Test', 'Test', '"test"')]))
        ],
        'two attributes' => [
            "#[Test]\n#[Test2]",
            fn() => new Method('test', new AttributeDTOCollection([new AttributeDTO('Test', 'Test'), new AttributeDTO('Test2', 'Test')]))
        ],
    ]);

    test('attribute offset', function() {
       $t = new AttributeDTOCollection();
       $a = new AttributeDTO('Test', 'Test');

       $t[2] = $a;

       expect($t->toArray())->toBeArray();
    });

    test('faulty attribute', function() {
        new AttributeDTOCollection(['']);
    })->throws(InvalidArgumentException::class);

    test('faulty attribute 2', function() {
        new AttributeDTOCollection(['']);
    })->throws(InvalidArgumentException::class);

    test('method prefixes', function($result, $method) {
        expect($method->getDefinition())->toContain($result);
    })->with([
        'one prefix' => ['public', new Method('name', prefixes: ['public'])],
        'two prefixes' => ['public static', new Method('name', prefixes: ['public', 'static'])],
    ]);

    test('method arguments', function($result, $methodClosure) {
        $method = $methodClosure();

        expect($method->getDefinition())->toContain($result);
    })->with([
        'one simple argument' => [
            '($test)',
            fn() => new Method('name', arguments: new PropertyDTOCollection([new PropertyDTO('test')]))
        ],
        'two simple arguments' => [
            '($test, $test2)',
            fn() => new Method('name', arguments: new PropertyDTOCollection([new PropertyDTO('test'), new PropertyDTO('test2')]))
        ],
        'one single prefixed argument' => [
            '(public $test)',
            fn() => new Method('name', arguments: new PropertyDTOCollection([new PropertyDTO('test', prefixes: ['public'])]))
        ],
        'one multiple prefixed argument' => [
            '(public readonly $test)',
            fn() => new Method('name', arguments: new PropertyDTOCollection([new PropertyDTO('test', prefixes: ['public', 'readonly'])]))
        ],
        'one single type argument' => [
            '(string $test)',
            fn() => new Method('name', arguments: new PropertyDTOCollection([new PropertyDTO('test', types: new ScalarOrClassDTOCollection([new ScalarOrClassDTO('string')]))]))
        ],
        'one multiple types argument' => [
            '(string|Test $test)',
            fn() => new Method('name', arguments: new PropertyDTOCollection([new PropertyDTO('test', types: new ScalarOrClassDTOCollection([new ScalarOrClassDTO('string'), new ScalarOrClassDTO('Test', 'Xwero\\Codestarter\\Test')]))]))
        ],
        'one default argument' => [
            '($test = [])',
            fn() => new Method('name', arguments: new PropertyDTOCollection([new PropertyDTO('test', default: '[]')]))
        ],
        'one everything argument' => [
            '(public string $test = [])',
            fn() => new Method('name', arguments: new PropertyDTOCollection([new PropertyDTO('test', prefixes: ['public'], types: new ScalarOrClassDTOCollection([new ScalarOrClassDTO('string')]), default: '[]')]))
        ],
    ]);

    test('method return types', function($result, $methodClosure) {
        $method = $methodClosure();

       expect($method->getDefinition())->toContain($result);
    })->with([
        'one return type' => [
            ': string',
            fn() => new Method('name', returnTypes: new ScalarOrClassDTOCollection([new ScalarOrClassDTO('string')]))
        ],
        'two return type' => [
            ': string|Test',
            fn() => new Method('name', returnTypes: new ScalarOrClassDTOCollection([new ScalarOrClassDTO('string'), new ScalarOrClassDTO('Test', 'Xwero\\Codestarter\\Test')]))
        ]
    ]);

    test('method body', function() {
        $result = 'return \'test\';';
       $method = new Method('name', content: $result);

       expect($method->getContent())->toContain($result);
    });
});
