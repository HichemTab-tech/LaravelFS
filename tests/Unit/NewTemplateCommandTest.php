<?php

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use HichemTabTech\LaravelFS\Console\NewTemplateCommand;
use Symfony\Component\Console\Tester\CommandTester;

beforeEach(function () {
    // Create a dummy NewTemplateCommand that overrides saveTemplateCommand() to capture the saved template.
    $this->command = new class extends NewTemplateCommand {
        public array|null $savedTemplate = null;
        public function saveTemplateCommand($templateName, $templateDescription, $templateCommand): void {
            $this->savedTemplate = [
                'name'        => $templateName,
                'description' => $templateDescription,
                'command'     => $templateCommand,
            ];
        }
    };

    $this->commandTester = new CommandTester($this->command);
});

test('new:template command generates and saves a template', function () {
    // Provide arguments: template-name and template-description, plus any options if needed.
    $input = [
        'template-name'        => 'test-template',
        'template-description' => 'A test template',
        // Simulate minimal required options (others will be empty/false)
        '--dev'                => false,
        '--git'                => false,
    ];
    $exitCode = $this->commandTester->execute($input);
    expect($exitCode)->toBe(0)
        ->and($this->command->savedTemplate)->toBeArray()
        ->and($this->command->savedTemplate['name'])->toEqual('test-template')
        ->and($this->command->savedTemplate['description'])->toEqual('A test template')
        ->and($this->command->savedTemplate['command'])->toContain('laravelfs new');
    // Ensure the generated command starts with "laravelfs new"
});
