<?php

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use HichemTabTech\LaravelFS\Console\NewCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

class DummyGitRepoCommand extends NewCommand
{
    public array $collectedCommands = [];

    /**
     * Override runCommands to capture the commands instead of executing them.
     */
    protected function runCommands(array $commands, $input, $output, ?string $workingPath = null, array $env = []): Process
    {
        $this->collectedCommands = $commands;
        // Return a dummy successful process.
        return new Process([]);
    }
}

beforeEach(function () {
    // Define the input with the "branch" option.
    $definition = new InputDefinition([
        new InputOption('branch', null, InputOption::VALUE_REQUIRED, 'The branch that should be created for a new repository', 'main'),
    ]);
    $this->input = new ArrayInput([], $definition);
    // Explicitly set branch option for determinism.
    $this->input->setOption('branch', 'main');
    $this->out = new BufferedOutput();
    $this->command = new DummyGitRepoCommand();
});

test('createRepository builds correct git commands', function () {
    $directory = '/path/to/app';
    $this->command->createRepository($directory, $this->input, $this->out);
    $expected = [
        'git init -q',
        'git add .',
        'git commit -q -m "Set up a fresh Laravel app"',
        "git branch -M main",
    ];
    expect($this->command->collectedCommands)->toEqual($expected);
});
