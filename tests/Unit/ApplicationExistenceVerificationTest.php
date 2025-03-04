<?php

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use Exception;
use HichemTabTech\LaravelFS\Console\NewCommand;
use RuntimeException;

// Dummy command that exposes verifyApplicationDoesntExist for testing.
class DummyNewCommandForExistence extends NewCommand
{
    // Expose verifyApplicationDoesntExist via a public method.
    public function callVerifyApplicationDoesntExist(string $directory): void
    {
        $this->verifyApplicationDoesntExist($directory);
    }

    // Override to simulate an existing directory if the name contains "exists".
    protected function verifyApplicationDoesntExist(string $directory): void
    {
        if (str_contains($directory, 'exists')) {
            throw new RuntimeException('Application already exists!');
        }
    }
}

beforeEach(function () {
    $this->command = new DummyNewCommandForExistence();
});

test('throws exception when directory exists', function () {
    expect(fn() => $this->command->callVerifyApplicationDoesntExist('path/to/exists-app'))
        ->toThrow(RuntimeException::class, 'Application already exists!');
});

test('passes when directory does not exist', function () {
    expect(fn() => $this->command->callVerifyApplicationDoesntExist('path/to/new-app'))
        ->not->toThrow(Exception::class);
});
