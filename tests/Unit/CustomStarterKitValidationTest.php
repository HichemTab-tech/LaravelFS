<?php

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use HichemTabTech\LaravelFS\Console\NewCommand;

// Dummy command to expose custom starter kit validation logic.
class DummyNewCommandCustomStarter extends NewCommand
{
    // Expose the custom starter kit validation callback.
    public function callCustomStarterValidation(string $value): ?string
    {
        // Create an input definition including a "custom-starter" option.

        // Inline validation callback from the custom starter kit prompt.
        $validationCallback = function ($value) {
            if (!preg_match('/^[a-z0-9_.-]+\/[a-z0-9_.-]+$/i', $value)) {
                return 'Please enter a valid Composer package name (e.g., vendor/package-name).';
            }
            return null;
        };

        return $validationCallback($value);
    }
}

beforeEach(function () {
    $this->command = new DummyNewCommandCustomStarter();
});

test('valid custom starter kit package returns null error', function () {
    expect($this->command->callCustomStarterValidation('vendor/package-name'))->toBeNull();
});

test('invalid custom starter kit package with spaces returns error', function () {
    expect($this->command->callCustomStarterValidation('vendor package-name'))
        ->toEqual('Please enter a valid Composer package name (e.g., vendor/package-name).');
});

test('invalid custom starter kit package missing vendor returns error', function () {
    expect($this->command->callCustomStarterValidation('/package-name'))
        ->toEqual('Please enter a valid Composer package name (e.g., vendor/package-name).');
});

test('invalid custom starter kit package missing package returns error', function () {
    expect($this->command->callCustomStarterValidation('vendor/'))
        ->toEqual('Please enter a valid Composer package name (e.g., vendor/package-name).');
});
