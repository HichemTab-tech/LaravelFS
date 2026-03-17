<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace HichemTabTech\LaravelFS\Console\Tests\Unit;

use HichemTabTech\LaravelFS\Console\NewCommand;

class DummyInstallationDirectoryCommand extends NewCommand
{
    public function getInstallationDirectoryPublic(string $name): string
    {
        return $this->getInstallationDirectory($name);
    }
}

test('it handles absolute paths correctly', function () {
    if (PHP_OS_FAMILY === 'Windows') {
        $this->markTestSkipped('This test is for Unix/Linux systems only.');
    }

    $command = new DummyInstallationDirectoryCommand();
    $absolutePath = '/tmp/my-app';
    expect($command->getInstallationDirectoryPublic($absolutePath))->toBe($absolutePath);

    $relativePath = 'my-app';
    expect($command->getInstallationDirectoryPublic($relativePath))->toBe(getcwd().'/'.$relativePath);

    expect($command->getInstallationDirectoryPublic('.'))->toBe('.');
});
