<?php

namespace Laravel\Installer\Console\Tests\Unit;

use Laravel\Installer\Console\NewCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use RuntimeException;

// Create a dummy command to expose our inline validation callback.
class DummyNewCommand extends NewCommand
{
    // Override the existence check to simulate a directory that already exists.
    protected function verifyApplicationDoesntExist(string $directory): void
    {
        // If the directory name contains "exists", simulate that it already exists.
        if (str_contains($directory, 'exists')) {
            throw new RuntimeException('Application already exists.');
        }
    }

    // Expose getInstallationDirectory for testing.
    public function getInstallationDirectory(string $name): string
    {
        return $name !== '.' ? getcwd() . '/' . $name : '.';
    }

    // Expose the validation logic from the project name prompt.
    public function callProjectNameValidation(string $name, bool $force = false): ?string
    {
        // Create an input definition with the "force" option.
        $definition = new InputDefinition([
            new InputOption('force', 'f', InputOption::VALUE_NONE, 'Force installation even if directory exists'),
        ]);

        // Create an ArrayInput using our custom definition.
        $input = new ArrayInput([], $definition);
        $input->setOption('force', $force);

        // Inline validation callback from the original command.
        $validationCallback = function ($value) use ($input) {
            if (preg_match('/[^\pL\pN\-_.]/', $value) !== 0) {
                return 'The name may only contain letters, numbers, dashes, underscores, and periods.';
            }

            if ($input->getOption('force') !== true) {
                try {
                    $this->verifyApplicationDoesntExist($this->getInstallationDirectory($value));
                } catch (RuntimeException) {
                    return 'Application already exists.';
                }
            }

            return null;
        };

        return $validationCallback($name);
    }
}

beforeEach(function () {
    $this->command = new DummyNewCommand();
});

test('valid project name returns null error', function () {
    expect($this->command->callProjectNameValidation('example-app'))->toBeNull();
});

test('invalid project name with illegal characters returns error', function () {
    expect($this->command->callProjectNameValidation('example@app'))
        ->toEqual('The name may only contain letters, numbers, dashes, underscores, and periods.');
});

test('existing project name returns error', function () {
    // "exists-app" triggers our simulated existing directory.
    expect($this->command->callProjectNameValidation('exists-app'))
        ->toEqual('Application already exists.');
});

test('force option bypasses existence check', function () {
    // With the force option enabled, even an "existing" name should pass.
    expect($this->command->callProjectNameValidation('exists-app', true))->toBeNull();
});
