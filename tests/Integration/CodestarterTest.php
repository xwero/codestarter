<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Xwero\Codestarter\Console\CodeStarterCommand;
use Xwero\Codestarter\DTO\GenerateDTO;
use Xwero\Codestarter\DTO\NewFileDTO;
use Xwero\Codestarter\Flows\Php\DTO\ImportDTO;
use Xwero\Codestarter\Flows\Php\TypedCollection\ImportDTOCollection;
use Xwero\Codestarter\Flows\Php\TypeFlow;

covers(
    CodeStarterCommand::class,
    TypeFlow::class,
    ImportDTOCollection::class,
    ImportDTO::class,
    NewFileDTO::class,
    GenerateDTO::class
);

function executeCommand(CommandTester &$commandTester, Command $command) : void {
    $commandTester->execute([
        'command' => $command->getName(),
        '--to-cli' => true,
    ], [
        'interactive' => true,
    ]);
}

describe('Codestarter command', function () {
    beforeEach(function () {
        $this->application = new Application();
        $this->command = new CodeStarterCommand(new Filesystem());

        $this->application->add($this->command);

        $this->commandTester = new CommandTester($this->command);
    });

    test('no group flow', function () {
        $this->commandTester->setInputs([]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('Select a group.');
    });

    test('no type flow', function () {
        $this->commandTester->setInputs(['Php']);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('Select a type.');
    });

    test('no filename flow', function () {
        $this->commandTester->setInputs(['Php', 1]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('Add the name of the file.');
    });

    test('basic class flow', function () {
       $this->commandTester->setInputs([
           'Php',
           0,
           'filename',
           'Xwero\\Codestarter\\name\\space',
           '', // 1
           '', // 2
           '', // 3
           '', // 4
           '', // 5
           '', // 6
           '', // 7
       ]);

       executeCommand($this->commandTester, $this->command);

       $output = $this->commandTester->getDisplay();

       expect($output)->toContain('What is the namespace?')
           ->and($output)->toContain('Has the class attributes?') // 1
           ->and($output)->toContain('Is the class abstract?')    // 2
           ->and($output)->toContain('Is the class final?')       // 3
           ->and($output)->toContain('Is the class readonly?')    // 4
           ->and($output)->toContain('Has the class a parent?')   // 5
           ->and($output)->toContain('Has the class interfaces?') // 6
           ->and($output)->toContain('Has the class methods?')    // 7
           ->and($output)->not()->toContain('use')
           ->and($output)->toContain('class filename')
           ->and($output)->toContain('src/name/space/filename.php');
    });

    test('class prefixes flow', function () {
        $this->commandTester->setInputs([
            'Php',
            0,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
            'y', // 2
            'y', // 3
            'y', // 4
            '', // 5
            '', // 6
            '', // 7
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the class attributes?') // 1
            ->and($output)->toContain('Is the class abstract?')    // 2
            ->and($output)->toContain('Is the class final?')       // 3
            ->and($output)->toContain('Is the class readonly?')    // 4
            ->and($output)->toContain('Has the class a parent?')   // 5
            ->and($output)->toContain('Has the class interfaces?') // 6
            ->and($output)->toContain('Has the class methods?')    // 7
            ->and($output)->not()->toContain('use')
            ->and($output)->toContain('abstract final readonly class filename')
            ->and($output)->toContain('src/name/space/filename.php');
    });

    test('class with attribute flow', function () {
        $this->commandTester->setInputs([
            'Php',
            0,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            'y', // 1
            'Xwero\Codestarter\Attribute\Test', // 2
            '', // 3
            '', // 4
            '', // 5
            '', // 6
            '', // 7
            '', // 8
            '', // 9
            '', // 10
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the class attributes?') // 1
            ->and($output)->toContain('What is the attribute class?') // 2
            ->and($output)->toContain('Does the attribute have arguments?') // 3
            ->and($output)->toContain('What is the attribute class?') // 4
            ->and($output)->toContain('Is the class abstract?')    // 5
            ->and($output)->toContain('Is the class final?')       // 6
            ->and($output)->toContain('Is the class readonly?')    // 7
            ->and($output)->toContain('Has the class a parent?')   // 8
            ->and($output)->toContain('Has the class interfaces?') // 9
            ->and($output)->toContain('Has the class methods?')    // 10
            ->and($output)->toContain('use Xwero\Codestarter\Attribute\Test')
            ->and($output)->toContain('#[Test]');
    });

    test('class with parent flow', function () {
        $this->commandTester->setInputs([
            'Php',
            0,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
            '', // 2
            '', // 3
            '', // 4
            'y', // 5
            'Xwero\Codestarter\Console\Test', // 6
            '', // 7
            '', // 8
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the class attributes?') // 1
            ->and($output)->toContain('Is the class abstract?')    // 2
            ->and($output)->toContain('Is the class final?')       // 3
            ->and($output)->toContain('Is the class readonly?')    // 4
            ->and($output)->toContain('Has the class a parent?')   // 5
            ->and($output)->toContain('What is the parent type?')  // 6
            ->and($output)->toContain('Has the class interfaces?') // 7
            ->and($output)->toContain('Has the class methods?')    // 8
            ->and($output)->toContain('use Xwero\Codestarter\Console\Test')
            ->and($output)->toContain('extends Test');
    });

    test('class with interface flow', function () {
        $this->commandTester->setInputs([
            'Php',
            0,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
            '', // 2
            '', // 3
            '', // 4
            '', // 5
            'y', // 6
            'Zxy', // 7
            '', // 8
            '', // 9
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the class attributes?') // 1
            ->and($output)->toContain('Is the class abstract?')    // 2
            ->and($output)->toContain('Is the class final?')       // 3
            ->and($output)->toContain('Is the class readonly?')    // 4
            ->and($output)->toContain('Has the class a parent?')   // 5
            ->and($output)->toContain('Has the class interfaces?') // 6
            ->and($output)->toContain('What is the interface type?') // 7
            ->and($output)->toContain('What is the interface type?') // 8
            ->and($output)->toContain('Has the class methods?')    // 9
            ->and($output)->toContain('use Tests\TestCase;')
            ->and($output)->toContain('class filename implements Zxy')
            ->and($output)->toContain('src/name/space/filename.php');
    });

    test('class with method flow', function () {
        $this->commandTester->setInputs([
            'Php',
            0,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
            '', // 2
            '', // 3
            '', // 4
            '', // 5
            '', // 6
            'y', // 7
            'test', // 8
            '', // 9
            'public', // 10
            '', // 11
            '', // 12
            '', // 13
            '', // 14
            '', // 15
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the class attributes?') // 1
            ->and($output)->toContain('Is the class abstract?')    // 2
            ->and($output)->toContain('Is the class final?')       // 3
            ->and($output)->toContain('Is the class readonly?')    // 4
            ->and($output)->toContain('Has the class a parent?')   // 5
            ->and($output)->toContain('Has the class interfaces?') // 6
            ->and($output)->toContain('Has the class methods?')    // 7
            ->and($output)->toContain('What is the method name?')  // 8
            ->and($output)->toContain('Has the method attributes?') // 9
            ->and($output)->toContain('What is the method visibility?') // 10
            ->and($output)->toContain('Is the method static?') // 11
            ->and($output)->toContain('has the method arguments?') // 12
            ->and($output)->toContain('Do you want to add a return type?') // 13
            ->and($output)->toContain('Do you want to add the body of the method?') // 14
            ->and($output)->toContain('What is the method name?') // 15
            ->and($output)->toContain('public function test()');
    });

    test('basic interface flow', function () {
        $this->commandTester->setInputs([
            'Php',
            1,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
            '', // 2
            '', // 3
            '', // 4
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the interface attributes?') // 1
            ->and($output)->toContain('Has the interface a parent?')   // 2
            ->and($output)->toContain('Has the interface interfaces?') // 3
            ->and($output)->toContain('Has the interface methods?')    // 4
            ->and($output)->not()->toContain('use')
            ->and($output)->toContain('interface filename')
            ->and($output)->toContain('src/name/space/filename.php');
    });

    test('interface with attribute flow', function () {
        $this->commandTester->setInputs([
            'Php',
            1,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            'y', // 1
            'Xwero\Codestarter\Attribute\Test', // 2
            '', // 3
            '', // 4
            '', // 5
            '', // 6
            '', // 7
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the interface attributes?') // 1
            ->and($output)->toContain('What is the attribute class?') // 2
            ->and($output)->toContain('Does the attribute have arguments?') // 3
            ->and($output)->toContain('What is the attribute class?') // 4
            ->and($output)->toContain('Has the interface a parent?')   // 5
            ->and($output)->toContain('Has the interface interfaces?') // 6
            ->and($output)->toContain('Has the interface methods?')    // 7
            ->and($output)->toContain('use Xwero\Codestarter\Attribute\Test')
            ->and($output)->toContain('#[Test]');
    });

    test('interface with parent flow', function () {
        $this->commandTester->setInputs([
            'Php',
            1,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
            'y', // 2
            'Xwero\Codestarter\Console\Test', // 3
            '', // 4
            '', // 5
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the interface attributes?') // 1
            ->and($output)->toContain('Has the interface a parent?')   // 2
            ->and($output)->toContain('What is the parent type?')  // 3
            ->and($output)->toContain('Has the interface interfaces?') // 4
            ->and($output)->toContain('Has the interface methods?')    // 5
            ->and($output)->toContain('use Xwero\Codestarter\Console\Test')
            ->and($output)->toContain('extends Test');
    });

    test('interface with interface flow', function () {
        $this->commandTester->setInputs([
            'Php',
            1,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
            '', // 2
            'y', // 3
            'Zxy', // 4
            '', // 5
            '', // 6
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the interface attributes?') // 1
            ->and($output)->toContain('Has the interface a parent?')   // 2
            ->and($output)->toContain('Has the interface interfaces?') // 3
            ->and($output)->toContain('What is the interface type?') // 4
            ->and($output)->toContain('What is the interface type?') // 5
            ->and($output)->toContain('Has the interface methods?')    // 6
            ->and($output)->toContain('interface filename implements Zxy')
            ->and($output)->toContain('src/name/space/filename.php');
    });

    test('interface with method flow', function () {
        $this->commandTester->setInputs([
            'Php',
            1,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
            '', // 2
            '', // 3
            'y', // 4
            'test', // 5
            '', // 6
            'public', // 7
            '', // 8
            '', // 9
            '', // 10
            '', // 11
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the interface attributes?') // 1
            ->and($output)->toContain('Has the interface a parent?')   // 2
            ->and($output)->toContain('Has the interface interfaces?') // 3
            ->and($output)->toContain('Has the interface methods?')    // 4
            ->and($output)->toContain('What is the method name?')  // 5
            ->and($output)->toContain('Has the method attributes?') // 6
            ->and($output)->toContain('What is the method visibility?') // 7
            ->and($output)->toContain('Is the method static?') // 8
            ->and($output)->toContain('has the method arguments?') // 9
            ->and($output)->toContain('Do you want to add a return type?') // 10
            ->and($output)->toContain('What is the method name?') // 11
            ->and($output)->toContain('public function test();');
    });

    test('basic trait flow', function () {
        $this->commandTester->setInputs([
            'Php',
            2,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the trait methods?')    // 1
            ->and($output)->not()->toContain('use')
            ->and($output)->toContain('trait filename')
            ->and($output)->toContain('src/name/space/filename.php');
    });

    test('trait with method flow', function () {
        $this->commandTester->setInputs([
            'Php',
            2,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            'y', // 1
            'test', // 2
            '', // 3
            'public', // 4
            '', // 5
            '', // 6
            '', // 7
            '', // 8
            '', // 9
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Has the trait methods?')    // 1
            ->and($output)->toContain('What is the method name?')  // 2
            ->and($output)->toContain('Has the method attributes?') // 3
            ->and($output)->toContain('What is the method visibility?') // 4
            ->and($output)->toContain('Is the method static?') // 5
            ->and($output)->toContain('has the method arguments?') // 6
            ->and($output)->toContain('Do you want to add a return type?') // 7
            ->and($output)->toContain('Do you want to add the body of the method?') // 8
            ->and($output)->toContain('What is the method name?') // 9
            ->and($output)->toContain('public function test()');
    });

    test('basic enum flow', function () {
        $this->commandTester->setInputs([
            'Php',
            3,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            '', // 1
            'Test', // 2
            '', // 3
            '', // 4
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Is it a backed enum?') // 1
            ->and($output)->toContain('What is the case?')   // 2
            ->and($output)->toContain('What is the case?')   // 3
            ->and($output)->toContain('Has the enum methods?')    // 4
            ->and($output)->not()->toContain('use')
            ->and($output)->toContain('enum filename')
            ->and($output)->toContain('case Test;')
            ->and($output)->toContain('src/name/space/filename.php');
    });

    test('backed enum flow', function () {
        $this->commandTester->setInputs([
            'Php',
            3,
            'filename',
            'Xwero\\Codestarter\\name\\space',
            'y', // 1
            'int', // 2
            'Test', // 3
            1, // 4
            '', // 5
            '', // 6
        ]);

        executeCommand($this->commandTester, $this->command);

        $output = $this->commandTester->getDisplay();

        expect($output)->toContain('What is the namespace?')
            ->and($output)->toContain('Is it a backed enum?') // 1
                ->and($output)->toContain('What is the return scalar?') // 2
            ->and($output)->toContain('What is the case?')   // 3
                ->and($output)->toContain('What is the case value?') // 4
            ->and($output)->toContain('What is the case?')   // 5
            ->and($output)->toContain('Has the enum methods?')    // 6
            ->and($output)->not()->toContain('use')
            ->and($output)->toContain('enum filename : int')
            ->and($output)->toContain('case Test = 1;')
            ->and($output)->toContain('src/name/space/filename.php');
    });
});
