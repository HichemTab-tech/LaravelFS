<?php

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use HichemTabTech\LaravelFS\Console\UseTemplateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

beforeEach(function () {
    // Create a dummy UseTemplateCommand that overrides getSavedTemplates() and runCommands() for testing.
    $this->command = new class extends UseTemplateCommand {
        public bool $runCommandsCalled = false;
        public string|null $lastCommand = null;

        protected function getSavedTemplates(bool $noInteract = false): array {
            return [
                'templates' => [
                    'template1' => [
                        'description' => 'A test template',
                        'command'     => 'laravelfs new <project-name> --react'
                    ]
                ],
                'path' => '/fake/path'
            ];
        }

        protected function runCommands(array $commands, InputInterface $input, OutputInterface $output, ?string $workingPath = null, array $env = []): Process {
            $this->runCommandsCalled = true;
            $this->lastCommand = $commands[0];
            return new Process([]);
        }
    };

    $this->commandTester = new CommandTester($this->command);
});

test('use command executes template command with replaced project name', function () {
    $input = [
        'template-name' => 'template1',
        'project-name'  => 'my-project',
    ];
    $exitCode = $this->commandTester->execute($input);
    expect($exitCode)->toBe(0)
        ->and($this->command->runCommandsCalled)->toBeTrue()
        ->and($this->command->lastCommand)->toContain('my-project');
    // Check that the template command has replaced "<project-name>" with "my-project"
});
