<?php

namespace Laravel\Installer\Console\Tests\Unit;

use Laravel\Installer\Console\NewCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * Dummy command that exposes a simulated version of promptForDatabaseOptions.
 */
class DummyDatabaseOptionsCommand extends NewCommand
{
    /**
     * Simulate the promptForDatabaseOptions method.
     *
     * @param ArrayInput $input
     * @param string|null $simulatedDatabase Optional simulated response from "select"
     * @param bool|null   $simulatedMigrate  Optional simulated response from "confirm"
     * @return array [database, migrate]
     */
    public function simulatePromptForDatabaseOptions(ArrayInput $input, ?string $simulatedDatabase = null, ?bool $simulatedMigrate = null): array
    {
        // Fetch the available database options.
        $databaseOptions = $this->databaseOptions();
        $defaultDatabase = collect($databaseOptions)->keys()->first();

        // If a starter kit is used, automatically set database to sqlite.
        if ($this->usingStarterKit($input)) {
            $input->setOption('database', 'sqlite');
            $migrate = false;
        } elseif (! $input->getOption('database') && $input->isInteractive()) {
            // Instead of calling the actual prompt, simulate the responses.
            $input->setOption('database', $simulatedDatabase ?? $defaultDatabase);

            if ($input->getOption('database') !== 'sqlite') {
                $migrate = $simulatedMigrate;
            } else {
                $migrate = true;
            }
        } else {
            $migrate = false;
        }

        return [$input->getOption('database') ?? $defaultDatabase, $migrate ?? false];
    }
}

beforeEach(function () {
    // Build an input definition with the necessary options.
    $definition = new InputDefinition([
        new InputOption('database', null, InputOption::VALUE_REQUIRED, 'The database driver your application will use'),
        new InputOption('react', null, InputOption::VALUE_NONE, 'Install the React Starter Kit'),
        new InputOption('vue', null, InputOption::VALUE_NONE, 'Install the Vue Starter Kit'),
        new InputOption('livewire', null, InputOption::VALUE_NONE, 'Install the Livewire Starter Kit'),
        new InputOption('custom-starter', null, InputOption::VALUE_NONE, 'Install a custom starter kit'),
    ]);
    $this->input = new ArrayInput([], $definition);
    $this->input->setInteractive(true);
    $this->command = new DummyDatabaseOptionsCommand();
});

test('using starter kit sets database to sqlite and disables migration', function () {
    // Simulate that a starter kit is being used.
    $this->input->setOption('react', true);
    // When using a starter kit, the database should automatically be set to 'sqlite'
    // and migration flag should be false.
    [$database, $migrate] = $this->command->simulatePromptForDatabaseOptions($this->input);
    expect($database)->toEqual('sqlite')
        ->and($migrate)->toBeFalse();
});

test('non-starter kit interactive mode with a non-sqlite selection and migration confirmed', function () {
    // Ensure no starter kit option is set.
    $this->input->setOption('react', false);
    $this->input->setOption('vue', false);
    $this->input->setOption('livewire', false);

    // Simulate the user selecting 'mysql' and confirming migration.
    [$database, $migrate] = $this->command->simulatePromptForDatabaseOptions($this->input, 'mysql', true);
    expect($database)->toEqual('mysql')
        ->and($migrate)->toBeTrue();
});

test('non-starter kit interactive mode with a sqlite selection should force migration true', function () {
    // Ensure no starter kit option is set.
    $this->input->setOption('react', false);
    $this->input->setOption('vue', false);
    $this->input->setOption('livewire', false);

    // Simulate the user selecting 'sqlite'. In this case, migrate should be set to true.
    [$database, $migrate] = $this->command->simulatePromptForDatabaseOptions($this->input, 'sqlite', false);
    expect($database)->toEqual('sqlite')
        ->and($migrate)->toBeTrue();
});
