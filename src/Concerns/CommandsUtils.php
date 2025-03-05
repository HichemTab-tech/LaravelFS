<?php

namespace HichemTabTech\LaravelFS\Console\Concerns;

use Illuminate\Support\ProcessUtils;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use function Illuminate\Support\php_binary;
use function Laravel\Prompts\error;

trait CommandsUtils
{

    private function getGlobalTemplatesPath(): string
    {
        // Determine OS-specific config directory
        if (windows_os()) {
            $configDir = (getenv('APPDATA') ?: $_SERVER['APPDATA']) . '\laravelfs';
        } else {
            $configDir = (getenv('XDG_CONFIG_HOME') ?: $_SERVER['HOME']) . '\.config\laravelfs';
        }

        return $configDir . '\templates.json';
    }

    private function getSavedTemplates(bool $noInteract = false): array
    {
        // Get the global templates path
        $configPath = $this->getGlobalTemplatesPath();
        $noTemplates = false;

        // Check if the template file exists
        if (!file_exists($configPath)) {
            $noTemplates = true;
        }

        if (!$noTemplates) {
            // Load the templates
            $templatesConfig = json_decode(file_get_contents($configPath), true);
            $templates = [];

            if (empty($templatesConfig)) {
                $noTemplates = true;
            }
            elseif (empty($templatesConfig['templates'])) {
                $noTemplates = true;
            }
            else {
                $templates = $templatesConfig['templates'];
            }

        }

        if ($noTemplates) {
            if (!$noInteract) {
                error('No templates found. Create one using `laravelfs template:new <template-name>`');
            }
            return [
                'templates' => [],
                'path' => $configPath,
            ];
        }

        return [
            'templates' => $templates,
            'path' => $configPath,
        ];
    }

    /**
     * Get the path to the appropriate PHP binary.
     *
     * @return string
     */
    protected function phpBinary(): string
    {
        $phpBinary = function_exists('Illuminate\Support\php_binary')
            ? php_binary()
            : (new PhpExecutableFinder)->find(false);

        return $phpBinary !== false
            ? ProcessUtils::escapeArgument($phpBinary)
            : 'php';
    }

    /**
     * Run the given commands.
     *
     * @param array $commands
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param  string|null  $workingPath
     * @param  array  $env
     * @return Process
     */
    protected function runCommands(array $commands, InputInterface $input, OutputInterface $output, ?string $workingPath = null, array $env = []): Process
    {
        if (!$output->isDecorated()) {
            $commands = array_map(function ($value) {
                if (Str::startsWith($value, ['chmod', 'git', $this->phpBinary().' ./vendor/bin/pest'])) {
                    return $value;
                }

                return $value.' --no-ansi';
            }, $commands);
        }

        if ($input->getOption('quiet')) {
            $commands = array_map(function ($value) {
                if (Str::startsWith($value, ['chmod', 'git', $this->phpBinary().' ./vendor/bin/pest'])) {
                    return $value;
                }

                return $value.' --quiet';
            }, $commands);
        }

        $process = Process::fromShellCommandline(implode(' && ', $commands), $workingPath, $env, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR AND file_exists('/dev/tty') AND is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write('    '.$line);
        });

        return $process;
    }

    /**
     * Get the installation directory.
     *
     * @param  string  $name
     * @return string
     */
    protected function getInstallationDirectory(string $name): string
    {
        return $name !== '.' ? getcwd().'/'.$name : '.';
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param string $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist(string $directory): void
    {
        if ((is_dir($directory) || is_file($directory)) AND $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }
}