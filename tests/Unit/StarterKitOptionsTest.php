<?php

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use HichemTabTech\LaravelFS\Console\NewCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

// Dummy command to simulate prompt behavior for Breeze and Jetstream options.
class DummyStarterKitOptionsCommand extends NewCommand
{
    /**
     * Simulate promptForBreezeOptions.
     *
     * @param ArrayInput $input
     * @param array $simulatedResponses
     */
    public function simulatePromptForBreezeOptions(ArrayInput $input, array $simulatedResponses = []): void
    {
        // If the stack option is not preset, simulate a selection.
        if (!$input->getOption('stack')) {
            $input->setOption('stack', $simulatedResponses['stack'] ?? 'blade');
        }

        // For react or vue stacks, simulate a multiselect for optional features.
        if (in_array($input->getOption('stack'), ['react', 'vue'])) {
            foreach ($simulatedResponses['features'] ?? [] as $feature) {
                $input->setOption($feature, true);
            }
        } elseif (in_array($input->getOption('stack'), ['blade', 'livewire', 'livewire-functional'])) {
            // Simulate confirmation for dark mode support.
            $input->setOption('dark', $simulatedResponses['dark'] ?? false);
        }
    }

    /**
     * Simulate promptForJetstreamOptions.
     *
     * @param ArrayInput $input
     * @param array $simulatedResponses
     */
    public function simulatePromptForJetstreamOptions(ArrayInput $input, array $simulatedResponses = []): void
    {
        // If no stack is set, simulate selecting the default.
        if (!$input->getOption('stack')) {
            $input->setOption('stack', $simulatedResponses['stack'] ?? 'livewire');
        }

        // Simulate multiselect for Jetstream features.
        foreach ($simulatedResponses['features'] ?? [] as $feature) {
            $input->setOption($feature, true);
        }
    }
}

beforeEach(function () {
    // Define the necessary options for both Breeze and Jetstream.
    $definition = new InputDefinition([
        new InputOption('stack', null, InputOption::VALUE_REQUIRED),
        new InputOption('dark', null, InputOption::VALUE_NONE),
        new InputOption('ssr', null, InputOption::VALUE_NONE),
        new InputOption('typescript', null, InputOption::VALUE_NONE),
        new InputOption('eslint', null, InputOption::VALUE_NONE),
        new InputOption('api', null, InputOption::VALUE_NONE),
        new InputOption('verification', null, InputOption::VALUE_NONE),
        new InputOption('teams', null, InputOption::VALUE_NONE),
    ]);
    $this->input = new ArrayInput([], $definition);
    $this->command = new DummyStarterKitOptionsCommand();
});

test('simulate prompt for Breeze options for react stack with all optional features', function () {
    $responses = [
        'stack' => 'react',
        'features' => ['dark', 'ssr', 'typescript', 'eslint'],
    ];
    $this->command->simulatePromptForBreezeOptions($this->input, $responses);
    expect($this->input->getOption('stack'))->toEqual('react')
        ->and($this->input->getOption('dark'))->toBeTrue()
        ->and($this->input->getOption('ssr'))->toBeTrue()
        ->and($this->input->getOption('typescript'))->toBeTrue()
        ->and($this->input->getOption('eslint'))->toBeTrue();
});

test('simulate prompt for Breeze options for blade stack with dark mode confirmation false', function () {
    $responses = [
        'stack' => 'blade',
        'dark' => false,
    ];
    $this->command->simulatePromptForBreezeOptions($this->input, $responses);
    expect($this->input->getOption('stack'))->toEqual('blade')
        ->and($this->input->getOption('dark'))->toBeFalse();
});

test('simulate prompt for Jetstream options with inertia stack and selected features', function () {
    $responses = [
        'stack' => 'inertia',
        'features' => ['api', 'dark', 'teams', 'verification', 'ssr'],
    ];
    $this->command->simulatePromptForJetstreamOptions($this->input, $responses);
    expect($this->input->getOption('stack'))->toEqual('inertia')
        ->and($this->input->getOption('api'))->toBeTrue()
        ->and($this->input->getOption('dark'))->toBeTrue()
        ->and($this->input->getOption('teams'))->toBeTrue()
        ->and($this->input->getOption('verification'))->toBeTrue()
        ->and($this->input->getOption('ssr'))->toBeTrue();
});
