<?php

namespace Laravel\Installer\Console\Tests\Unit;

use Laravel\Installer\Console\NewCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;

// Dummy command to simulate interactive selection logic.
class DummyInteractiveSelectionCommand extends NewCommand
{
    /**
     * Simulate the interactive selection match block.
     *
     * @param string $choice
     * @param ArrayInput $input
     * @param BufferedOutput $output
     */
    public function simulateInteractiveSelection(string $choice, ArrayInput $input, BufferedOutput $output): void {
        match ($choice) {
            'react' => $input->setOption('react', true),
            'vue' => $input->setOption('vue', true),
            'livewire' => $input->setOption('livewire', true),
            'breeze' => $input->setOption('breeze', true),
            'jetstream' => $input->setOption('jet', true),
            'custom' => (function () use ($output, $input) {
                $output->writeln('<fg=blue>INFO</> Your custom starter must be a Composer package of type "project", stored in a public repository, and published on Packagist.');
                // Simulate prompting and receiving a custom package name.
                $input->setOption('custom-starter', 'vendor/package-name');
            })(),
            default => null,
        };
    }
}

beforeEach(function () {
    // Build an input definition that includes all the interactive options.
    $definition = new InputDefinition([
        new InputOption('react', null, InputOption::VALUE_NONE),
        new InputOption('vue', null, InputOption::VALUE_NONE),
        new InputOption('livewire', null, InputOption::VALUE_NONE),
        new InputOption('breeze', null, InputOption::VALUE_NONE),
        new InputOption('jet', null, InputOption::VALUE_NONE),
        new InputOption('custom-starter', null, InputOption::VALUE_REQUIRED),
    ]);
    $this->input = new ArrayInput([], $definition);
    $this->out = new BufferedOutput();
    $this->command = new DummyInteractiveSelectionCommand();
});

test('selecting react sets react option', function () {
    $this->command->simulateInteractiveSelection('react', $this->input, $this->out);
    expect($this->input->getOption('react'))->toBeTrue();
});

test('selecting vue sets vue option', function () {
    $this->command->simulateInteractiveSelection('vue', $this->input, $this->out);
    expect($this->input->getOption('vue'))->toBeTrue();
});

test('selecting livewire sets livewire option', function () {
    $this->command->simulateInteractiveSelection('livewire', $this->input, $this->out);
    expect($this->input->getOption('livewire'))->toBeTrue();
});

test('selecting breeze sets breeze option', function () {
    $this->command->simulateInteractiveSelection('breeze', $this->input, $this->out);
    expect($this->input->getOption('breeze'))->toBeTrue();
});

test('selecting jetstream sets jet option', function () {
    $this->command->simulateInteractiveSelection('jetstream', $this->input, $this->out);
    expect($this->input->getOption('jet'))->toBeTrue();
});

test('selecting custom sets custom-starter option', function () {
    $this->command->simulateInteractiveSelection('custom', $this->input, $this->out);
    expect($this->input->getOption('custom-starter'))->toEqual('vendor/package-name');
});
