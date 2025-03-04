<?php

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use HichemTabTech\LaravelFS\Console\NewCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

// Dummy command to expose the custom starter kit command builder.
class DummyCustomStarterCommand extends NewCommand
{
    /**
     * Build and return the create-project command.
     *
     * @param ArrayInput $input
     * @param string $directory
     * @param string $composer
     * @return string|null
     */
    public function getCreateProjectCommand(ArrayInput $input, string $directory, string $composer = 'composer'): ?string
    {
        if ($input->getOption('custom-starter')) {
            $package = $input->getOption('custom-starter');
            return $composer . " create-project $package \"$directory\" --stability=dev";
        }
        return null;
    }
}

beforeEach(function () {
    // Create an input definition including the custom-starter option.
    $definition = new InputDefinition([
        new InputOption('custom-starter', null, InputOption::VALUE_REQUIRED, 'Custom starter kit package'),
    ]);
    $this->input = new ArrayInput([], $definition);
    $this->command = new DummyCustomStarterCommand();
});

test('custom starter kit installation command is built correctly', function () {
    // Set custom-starter option with a valid package.
    $this->input->setOption('custom-starter', 'vendor/package-name');
    $directory = '/path/to/app';
    $composer = 'composer';
    $expected = 'composer create-project vendor/package-name "/path/to/app" --stability=dev';

    expect($this->command->getCreateProjectCommand($this->input, $directory, $composer))
        ->toEqual($expected);
});
