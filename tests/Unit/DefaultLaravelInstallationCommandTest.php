<?php

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use HichemTabTech\LaravelFS\Console\NewCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class DummyDefaultCommand extends NewCommand
{
    /**
     * Expose the logic for building the default installation command.
     *
     * @param ArrayInput $input
     * @param string $directory
     * @param string $composer
     * @return string
     */
    public function getDefaultCreateProjectCommand(ArrayInput $input, string $directory, string $composer = 'composer'): string
    {
        $version = $this->getVersion($input);
        return $composer . " create-project laravel/laravel \"$directory\" $version --remove-vcs --prefer-dist --no-scripts";
    }
}

beforeEach(function () {
    // Build an input definition with the necessary options.
    $definition = new InputDefinition([
        new InputOption('custom-starter', null, InputOption::VALUE_REQUIRED, 'Custom starter kit package'),
        new InputOption('react', null, InputOption::VALUE_NONE),
        new InputOption('vue', null, InputOption::VALUE_NONE),
        new InputOption('livewire', null, InputOption::VALUE_NONE),
        new InputOption('dev', null, InputOption::VALUE_NONE, 'Install the dev version'),
    ]);
    $this->input = new ArrayInput([], $definition);
    $this->command = new DummyDefaultCommand();
});

test('default laravel installation command without dev option', function () {
    // Ensure no custom starter or stack options are set.
    $this->input->setOption('custom-starter', null);
    $this->input->setOption('react', false);
    $this->input->setOption('vue', false);
    $this->input->setOption('livewire', false);
    $this->input->setOption('dev', false);

    $directory = '/path/to/app';
    $composer = 'composer';
    // Expect an empty version string when "dev" is not enabled.
    $expected = 'composer create-project laravel/laravel "/path/to/app"  --remove-vcs --prefer-dist --no-scripts';
    expect($this->command->getDefaultCreateProjectCommand($this->input, $directory, $composer))
        ->toEqual($expected);
});

test('default laravel installation command with dev option enabled', function () {
    // With the dev option set, the version should be "dev-master".
    $this->input->setOption('custom-starter', null);
    $this->input->setOption('react', false);
    $this->input->setOption('vue', false);
    $this->input->setOption('livewire', false);
    $this->input->setOption('dev', true);

    $directory = '/path/to/app';
    $composer = 'composer';
    $expected = 'composer create-project laravel/laravel "/path/to/app" dev-master --remove-vcs --prefer-dist --no-scripts';
    expect($this->command->getDefaultCreateProjectCommand($this->input, $directory, $composer))
        ->toEqual($expected);
});
