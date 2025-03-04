<?php

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use HichemTabTech\LaravelFS\Console\NewCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

class DummyPestCommand extends NewCommand
{
    public array $collectedCommands = [];
    public array $capturedEnv = [];
    public array $replacedFiles = [];
    public array $committedMessages = [];

    // Override findComposer to return a fixed value.
    protected function findComposer(): string
    {
        return 'composer';
    }

    // Override phpBinary to return a fixed value.
    protected function phpBinary(): string
    {
        return 'php';
    }

    // Override runCommands to capture the commands and environment.
    protected function runCommands(array $commands, $input, $output, ?string $workingPath = null, array $env = []): Process
    {
        $this->collectedCommands = $commands;
        $this->capturedEnv = $env;
        return new Process([]);
    }

    // Override replaceFile to capture file replacement parameters.
    protected function replaceFile(string $replace, string $file): void
    {
        $this->replacedFiles[] = ['replace' => $replace, 'file' => $file];
    }

    // Override replaceInFile (not expected to be called in this scenario).
    protected function replaceInFile(string|array $search, string|array $replace, string $file): void
    {
        // Capture call if needed.
    }

    // Override deleteFile (not expected in this scenario).
    protected function deleteFile(string $file): void
    {
        // Capture call if needed.
    }

    // Override commitChanges to capture the commit message.
    protected function commitChanges(string $message, string $directory, $input, $output): void
    {
        $this->committedMessages[] = $message;
    }
}

beforeEach(function () {
    // Build an input definition with the options needed.
    $definition = new InputDefinition([
        new InputOption('react', null, InputOption::VALUE_NONE),
        new InputOption('vue', null, InputOption::VALUE_NONE),
        new InputOption('livewire', null, InputOption::VALUE_NONE),
        new InputOption('phpunit', null, InputOption::VALUE_NONE),
    ]);
    $this->input = new ArrayInput([], $definition);
    $this->out = new BufferedOutput();
    $this->command = new DummyPestCommand();
});

test('pest installation workflow without react/vue/livewire options', function () {
    $directory = '/path/to/app';

    // Ensure no starter kit options are set.
    $this->input->setOption('react', false);
    $this->input->setOption('vue', false);
    $this->input->setOption('livewire', false);
    $this->input->setOption('phpunit', false);

    // Call the installPest method.
    $this->command->installPest($directory, $this->input, $this->out);

    // Expected base commands.
    $expectedCommands = [
        'composer remove phpunit/phpunit --dev --no-update',
        'composer require pestphp/pest pestphp/pest-plugin-laravel --no-update --dev',
        'composer update',
        'php ./vendor/bin/pest --init',
    ];

    expect($this->command->collectedCommands)->toEqual($expectedCommands)
        ->and($this->command->capturedEnv)->toEqual(['PEST_NO_SUPPORT' => 'true'])
        ->and($this->command->replacedFiles)->toEqual([
            ['replace' => 'pest/Feature.php', 'file' => $directory . '/tests/Feature/ExampleTest.php'],
            ['replace' => 'pest/Unit.php', 'file' => $directory . '/tests/Unit/ExampleTest.php'],
        ])
        ->and($this->command->committedMessages)->toContain('Install Pest');
});
