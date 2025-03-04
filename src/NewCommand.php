<?php

namespace Laravel\Installer\Console;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\ProcessUtils;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

use function Illuminate\Support\php_binary;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class NewCommand extends Command
{
    use Concerns\ConfiguresPrompts;
    use Concerns\InteractsWithHerdOrValet;

    /**
     * The Composer instance.
     *
     * @var Composer
     */
    protected Composer $composer;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('new')
            ->setDescription('Create a new Laravel application')
            ->addArgument('name', InputArgument::REQUIRED)
            ->addOption('dev', null, InputOption::VALUE_NONE, 'Install the latest "development" release')
            ->addOption('git', null, InputOption::VALUE_NONE, 'Initialize a Git repository')
            ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'The branch that should be created for a new repository', $this->defaultBranch())
            ->addOption('github', null, InputOption::VALUE_OPTIONAL, 'Create a new repository on GitHub', false)
            ->addOption('organization', null, InputOption::VALUE_REQUIRED, 'The GitHub organization to create the new repository for')
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'The database driver your application will use')
            //from old installer
            ->addOption('stack', null, InputOption::VALUE_OPTIONAL, 'The Breeze / Jetstream stack that should be installed')
            ->addOption('breeze', null, InputOption::VALUE_NONE, 'Installs the Laravel Breeze scaffolding')
            ->addOption('jet', null, InputOption::VALUE_NONE, 'Installs the Laravel Jetstream scaffolding')
            ->addOption('dark', null, InputOption::VALUE_NONE, 'Indicate whether Breeze or Jetstream should be scaffolded with dark mode support')
            ->addOption('typescript', null, InputOption::VALUE_NONE, 'Indicate whether Breeze should be scaffolded with TypeScript support')
            ->addOption('eslint', null, InputOption::VALUE_NONE, 'Indicate whether Breeze should be scaffolded with ESLint and Prettier support')
            ->addOption('ssr', null, InputOption::VALUE_NONE, 'Indicate whether Breeze or Jetstream should be scaffolded with Inertia SSR support')
            ->addOption('api', null, InputOption::VALUE_NONE, 'Indicates whether Jetstream should be scaffolded with API support')
            ->addOption('teams', null, InputOption::VALUE_NONE, 'Indicates whether Jetstream should be scaffolded with team support')
            ->addOption('verification', null, InputOption::VALUE_NONE, 'Indicates whether Jetstream should be scaffolded with email verification support')

            ->addOption('custom-starter', null, InputOption::VALUE_REQUIRED, 'Custom Starter (Provide your own starter-kit)')

            // from new installer
            ->addOption('react', null, InputOption::VALUE_NONE, 'Install the React Starter Kit')
            ->addOption('vue', null, InputOption::VALUE_NONE, 'Install the Vue Starter Kit')
            ->addOption('livewire', null, InputOption::VALUE_NONE, 'Install the Livewire Starter Kit')
            ->addOption('livewire-class-components', null, InputOption::VALUE_NONE, 'Generate stand-alone Livewire class components')
            ->addOption('workos', null, InputOption::VALUE_NONE, 'Use WorkOS for authentication')
            ->addOption('pest', null, InputOption::VALUE_NONE, 'Install the Pest testing framework')
            ->addOption('phpunit', null, InputOption::VALUE_NONE, 'Install the PHPUnit testing framework')
            ->addOption('npm', null, InputOption::VALUE_NONE, 'Install and build NPM dependencies')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    /**
     * Interact with the user before validating the input.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        parent::interact($input, $output);

        $this->configurePrompts($input, $output);

        $output->write(PHP_EOL.'  <fg=red> _                               _
  | |                             | |
  | |     __ _ _ __ __ ___   _____| |
  | |    / _` |  __/ _` \ \ / / _ \ |
  | |___| (_| | | | (_| |\ V /  __/ |
  |______\__,_|_|  \__,_| \_/ \___|_|</>'.PHP_EOL.PHP_EOL);

        $this->ensureExtensionsAreAvailable();

        if (! $input->getArgument('name')) {
            $input->setArgument('name', text(
                label: 'What is the name of your project?',
                placeholder: 'E.g. example-app',
                required: 'The project name is required.',
                validate: function ($value) use ($input) {
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
                },
            ));
        }

        if ($input->getOption('force') !== true) {
            $this->verifyApplicationDoesntExist(
                $this->getInstallationDirectory($input->getArgument('name'))
            );
        }

        if (! $input->getOption('react') && ! $input->getOption('vue') && ! $input->getOption('livewire') && ! $input->getOption('breeze') && ! $input->getOption('jet') && ! $input->getOption('custom-starter')) {
            match (select(
                label: 'Which starter kit would you like to install?',
                options: [
                    'none' => 'None',
                    'react' => 'new React Starter Kit',
                    'vue' => 'New Vue Starter Kit',
                    'livewire' => 'New Livewire Starter Kit',
                    'breeze' => 'Laravel Breeze',
                    'jetstream' => 'Laravel Jetstream',
                    'custom' => 'Custom Starter (Provide your own starter-kit)',
                ],
                default: 'none',
            )) {
                'react' => $input->setOption('react', true),
                'vue' => $input->setOption('vue', true),
                'livewire' => $input->setOption('livewire', true),
                'breeze' => $input->setOption('breeze', true),
                'jetstream' => $input->setOption('jet', true),
                'custom' => (function () use ($output, $input) {
                    $output->writeln('<fg=blue>INFO</> Your custom starter must be a Composer package of type "project", stored in a public repository (e.g., GitHub, GitLab), and published on Packagist.');
                    $input->setOption('custom-starter', text(
                            label: 'Provide the Composer package (type: project) for the starter kit:',
                            placeholder: 'E.g. vendor/package-name',
                            required: 'You must provide a valid Composer package of type "project".',
                            validate: function ($value) use ($input) {
                                if (!preg_match('/^[a-z0-9_.-]+\/[a-z0-9_.-]+$/i', $value)) {
                                    return 'Please enter a valid Composer package name (e.g., vendor/package-name).';
                                }

                                return null;
                            },
                        )
                    );
                })(),
                default => null,
            };

            if ($input->getOption('breeze')) {
                $this->promptForBreezeOptions($input);
            } elseif ($input->getOption('jet')) {
                $this->promptForJetstreamOptions($input);
            }

            if ($this->usingStarterKit($input)) {
                match (select(
                    label: 'Which authentication provider do you prefer?',
                    options: [
                        'laravel' => "Laravel's built-in authentication",
                        'workos' => 'WorkOS (Requires WorkOS account)',
                    ],
                    default: 'laravel',
                )) {
                    'laravel' => $input->setOption('workos', false),
                    'workos' => $input->setOption('workos', true),
                    default => null,
                };
            }

            if ($input->getOption('livewire') && ! $input->getOption('workos')) {
                $input->setOption('livewire-class-components', ! confirm(
                    label: 'Would you like to use Laravel Volt?',
                ));
            }
        }

        if ($this->usingStarterKit($input)) {
            if (! $input->getOption('phpunit') &&
                ! $input->getOption('pest')) {
                $input->setOption('pest', select(
                    label: 'Which testing framework do you prefer?',
                    options: ['Pest', 'PHPUnit'],
                    default: 'Pest',
                ) === 'Pest');
            }
        } elseif ($this->usingLegacyStarterKit($input)) {
            if (! $input->getOption('phpunit') && ! $input->getOption('pest')) {
                $input->setOption('pest', select(
                        label: 'Which testing framework do you prefer?',
                        options: ['Pest', 'PHPUnit'],
                        default: 'Pest',
                    ) === 'Pest');
            }
        } else {
            $input->setOption('phpunit', true);
        }
    }


    /**
     * Ensure that the required PHP extensions are installed.
     *
     * @return void
     * @throws RuntimeException
     */
    protected function ensureExtensionsAreAvailable(): void
    {
        $availableExtensions = get_loaded_extensions();

        $missingExtensions = collect([
            'ctype',
            'filter',
            'hash',
            'mbstring',
            'openssl',
            'session',
            'tokenizer',
        ])->reject(fn ($extension) => in_array($extension, $availableExtensions));

        if ($missingExtensions->isEmpty()) {
            return;
        }

        throw new RuntimeException(
            sprintf('The following PHP extensions are required but are not installed: %s', $missingExtensions->join(', ', ', and '))
        );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->validateDatabaseOption($input);
        $this->validateStackOption($input);

        $name = rtrim($input->getArgument('name'), '/\\');

        $directory = $this->getInstallationDirectory($name);

        $this->composer = new Composer(new Filesystem(), $directory);

        $version = $this->getVersion($input);

        if (! $input->getOption('force')) {
            $this->verifyApplicationDoesntExist($directory);
        }

        if ($input->getOption('force') && $directory === '.') {
            throw new RuntimeException('Cannot use --force option when using current directory for installation!');
        }

        $composer = $this->findComposer();
        $phpBinary = $this->phpBinary();

        $createProjectCommand = $composer." create-project laravel/laravel \"$directory\" $version --remove-vcs --prefer-dist --no-scripts";

        if ($input->getOption('custom-starter')) {
            $package = $input->getOption('custom-starter');
            $createProjectCommand = $composer." create-project $package \"$directory\" --stability=dev";
        } else {
            $stackSlug = match (true) {
                $input->getOption('react') => 'react',
                $input->getOption('vue') => 'vue',
                $input->getOption('livewire') => 'livewire',
                default => null
            };

            if ($stackSlug) {
                $createProjectCommand = $composer . " create-project laravel/$stackSlug-starter-kit \"$directory\" --stability=dev";

                if ($input->getOption('livewire-class-components')) {
                    $createProjectCommand = str_replace(" laravel/$stackSlug-starter-kit ", " laravel/$stackSlug-starter-kit:dev-components ", $createProjectCommand);
                }

                if ($input->getOption('workos')) {
                    $createProjectCommand = str_replace(" laravel/$stackSlug-starter-kit ", " laravel/$stackSlug-starter-kit:dev-workos ", $createProjectCommand);
                }
            }
        }

        $commands = [
            $createProjectCommand,
            $composer." run post-root-package-install -d \"$directory\"",
            $phpBinary." \"$directory/artisan\" key:generate --ansi",
        ];

        if ($directory != '.' && $input->getOption('force')) {
            if (PHP_OS_FAMILY == 'Windows') {
                array_unshift($commands, "(if exist \"$directory\" rd /s /q \"$directory\")");
            } else {
                array_unshift($commands, "rm -rf \"$directory\"");
            }
        }

        if (PHP_OS_FAMILY != 'Windows') {
            $commands[] = "chmod 755 \"$directory/artisan\"";
        }

        if (($process = $this->runCommands($commands, $input, $output))->isSuccessful()) {
            if ($name !== '.') {
                $this->replaceInFile(
                    'APP_URL=http://localhost',
                    'APP_URL='.$this->generateAppUrl($name),
                    $directory.'/.env'
                );

                [$database, $migrate] = $this->promptForDatabaseOptions($input);

                $this->configureDefaultDatabaseConnection($directory, $database, $name);

                if ($migrate) {
                    if ($database === 'sqlite') {
                        touch($directory.'/database/database.sqlite');
                    }

                    $commands = [
                        trim(sprintf(
                            $this->phpBinary().' artisan migrate %s',
                            ! $input->isInteractive() ? '--no-interaction' : '',
                        )),
                    ];

                    $this->runCommands($commands, $input, $output, workingPath: $directory);
                }
            }

            if ($input->getOption('git') || $input->getOption('github') !== false) {
                $this->createRepository($directory, $input, $output);
            }

            if ($input->getOption('breeze')) {
                $this->installBreeze($directory, $input, $output);
            } elseif ($input->getOption('jet')) {
                $this->installJetstream($directory, $input, $output);
            } elseif ($input->getOption('pest')) {
                $this->installPest($directory, $input, $output);
            }

            if ($input->getOption('github') !== false) {
                $this->pushToGitHub($name, $directory, $input, $output);
                $output->writeln('');
            }

            $this->configureComposerDevScript();

            if ($input->getOption('pest')) {
                $output->writeln('');
            }

            $runNpm = $input->getOption('npm');

            if (! $input->getOption('npm') && $input->isInteractive()) {
                $runNpm = confirm(
                    label: 'Would you like to run <options=bold>npm install</> and <options=bold>npm run build</>?'
                );
            }

            if ($runNpm) {
                $this->runCommands(['npm install', 'npm run build'], $input, $output, workingPath: $directory);
            }

            $output->writeln("  <bg=blue;fg=white> INFO </> Application ready in <options=bold>[$name]</>. You can start your local development using:".PHP_EOL);
            $output->writeln('<fg=gray>➜</> <options=bold>cd '.$name.'</>');

            if (! $runNpm) {
                $output->writeln('<fg=gray>➜</> <options=bold>npm install && npm run build</>');
            }

            if ($this->isParkedOnHerdOrValet($directory)) {
                $url = $this->generateAppUrl($name);
                $output->writeln('<fg=gray>➜</> Open: <options=bold;href='.$url.'>'.$url.'</>');
            } else {
                $output->writeln('<fg=gray>➜</> <options=bold>composer run dev</>');
            }

            $output->writeln('');
            $output->writeln('  New to Laravel? Check out our <href=https://laravel.com/docs/installation#next-steps>documentation</>. <options=bold>Build something amazing!</>');
            $output->writeln('');
        }

        return $process->getExitCode();
    }

    /**
     * Return the local machine's default Git branch if set or default to `main`.
     *
     * @return string
     */
    protected function defaultBranch(): string
    {
        $process = new Process(['git', 'config', '--global', 'init.defaultBranch']);

        $process->run();

        $output = trim($process->getOutput());

        return $process->isSuccessful() && $output ? $output : 'main';
    }

    /**
     * Configure the default database connection.
     *
     * @param  string  $directory
     * @param  string  $database
     * @param  string  $name
     * @return void
     */
    protected function configureDefaultDatabaseConnection(string $directory, string $database, string $name): void
    {
        $this->pregReplaceInFile(
            '/DB_CONNECTION=.*/',
            'DB_CONNECTION='.$database,
            $directory.'/.env'
        );

        $this->pregReplaceInFile(
            '/DB_CONNECTION=.*/',
            'DB_CONNECTION='.$database,
            $directory.'/.env.example'
        );

        if ($database === 'sqlite') {
            $environment = file_get_contents($directory.'/.env');

            // If database options aren't commented, comment them for SQLite...
            if (! str_contains($environment, '# DB_HOST=127.0.0.1')) {
                $this->commentDatabaseConfigurationForSqlite($directory);

                return;
            }

            return;
        }

        // Any commented database configuration options should be uncommented when not on SQLite...
        $this->uncommentDatabaseConfiguration($directory);

        $defaultPorts = [
            'pgsql' => '5432',
            'sqlsrv' => '1433',
        ];

        if (isset($defaultPorts[$database])) {
            $this->replaceInFile(
                'DB_PORT=3306',
                'DB_PORT='.$defaultPorts[$database],
                $directory.'/.env'
            );

            $this->replaceInFile(
                'DB_PORT=3306',
                'DB_PORT='.$defaultPorts[$database],
                $directory.'/.env.example'
            );
        }

        $this->replaceInFile(
            'DB_DATABASE=laravel',
            'DB_DATABASE='.str_replace('-', '_', strtolower($name)),
            $directory.'/.env'
        );

        $this->replaceInFile(
            'DB_DATABASE=laravel',
            'DB_DATABASE='.str_replace('-', '_', strtolower($name)),
            $directory.'/.env.example'
        );
    }

    /**
     * Comment the irrelevant database configuration entries for SQLite applications.
     *
     * @param  string  $directory
     * @return void
     */
    protected function commentDatabaseConfigurationForSqlite(string $directory): void
    {
        $defaults = [
            'DB_HOST=127.0.0.1',
            'DB_PORT=3306',
            'DB_DATABASE=laravel',
            'DB_USERNAME=root',
            'DB_PASSWORD=',
        ];

        $this->replaceInFile(
            $defaults,
            collect($defaults)->map(fn ($default) => "# $default")->all(),
            $directory.'/.env'
        );

        $this->replaceInFile(
            $defaults,
            collect($defaults)->map(fn ($default) => "# $default")->all(),
            $directory.'/.env.example'
        );
    }

    /**
     * Uncomment the relevant database configuration entries for non SQLite applications.
     *
     * @param  string  $directory
     * @return void
     */
    protected function uncommentDatabaseConfiguration(string $directory): void
    {
        $defaults = [
            '# DB_HOST=127.0.0.1',
            '# DB_PORT=3306',
            '# DB_DATABASE=laravel',
            '# DB_USERNAME=root',
            '# DB_PASSWORD=',
        ];

        $this->replaceInFile(
            $defaults,
            collect($defaults)->map(fn ($default) => substr($default, 2))->all(),
            $directory.'/.env'
        );

        $this->replaceInFile(
            $defaults,
            collect($defaults)->map(fn ($default) => substr($default, 2))->all(),
            $directory.'/.env.example'
        );
    }

    /**
     * Install Laravel Breeze into the application.
     *
     * @param  string  $directory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function installBreeze(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $commands = array_filter([
            $this->findComposer().' require laravel/breeze --dev',
            trim(sprintf(
                $this->phpBinary().' artisan breeze:install %s %s %s %s %s %s',
                $input->getOption('stack'),
                $input->getOption('typescript') ? '--typescript' : '',
                $input->getOption('pest') ? '--pest' : '',
                $input->getOption('dark') ? '--dark' : '',
                $input->getOption('ssr') ? '--ssr' : '',
                $input->getOption('eslint') ? '--eslint' : '',
            )),
        ]);

        $this->runCommands($commands, $input, $output, workingPath: $directory);

        $this->commitChanges('Install Breeze', $directory, $input, $output);
    }

    /**
     * Install Laravel Jetstream into the application.
     *
     * @param  string  $directory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function installJetstream(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $commands = array_filter([
            $this->findComposer().' require laravel/jetstream',
            trim(sprintf(
                $this->phpBinary().' artisan jetstream:install %s %s %s %s %s %s %s',
                $input->getOption('stack'),
                $input->getOption('api') ? '--api' : '',
                $input->getOption('dark') ? '--dark' : '',
                $input->getOption('teams') ? '--teams' : '',
                $input->getOption('pest') ? '--pest' : '',
                $input->getOption('verification') ? '--verification' : '',
                $input->getOption('ssr') ? '--ssr' : '',
            )),
        ]);

        $this->runCommands($commands, $input, $output, workingPath: $directory);

        $this->commitChanges('Install Jetstream', $directory, $input, $output);
    }

    /**
     * Determine the default database connection.
     *
     * @param InputInterface $input
     * @return array
     */
    protected function promptForDatabaseOptions(InputInterface $input): array
    {
        $defaultDatabase = collect(
            $databaseOptions = $this->databaseOptions()
        )->keys()->first();

        if ($this->usingStarterKit($input)) {
            // Starter kits will already be migrated in post-composer create-project command...
            $migrate = false;

            $input->setOption('database', 'sqlite');
        }

        if (! $input->getOption('database') && $input->isInteractive()) {
            $input->setOption('database', select(
                label: 'Which database will your application use?',
                options: $databaseOptions,
                default: $defaultDatabase,
            ));

            if ($input->getOption('database') !== 'sqlite') {
                $migrate = confirm(
                    label: 'Default database updated. Would you like to run the default database migrations?'
                );
            } else {
                $migrate = true;
            }
        }

        return [$input->getOption('database') ?? $defaultDatabase, $migrate ?? $input->hasOption('database')];
    }

    /**
     * Get the available database options.
     *
     * @return array
     */
    protected function databaseOptions(): array
    {
        return collect([
            'sqlite' => ['SQLite', extension_loaded('pdo_sqlite')],
            'mysql' => ['MySQL', extension_loaded('pdo_mysql')],
            'mariadb' => ['MariaDB', extension_loaded('pdo_mysql')],
            'pgsql' => ['PostgreSQL', extension_loaded('pdo_pgsql')],
            'sqlsrv' => ['SQL Server', extension_loaded('pdo_sqlsrv')],
        ])
            ->sortBy(fn ($database) => $database[1] ? 0 : 1)
            ->map(fn ($database) => $database[0].($database[1] ? '' : ' (Missing PDO extension)'))
            ->all();
    }

    /**
     * Validate the database driver input.
     *
     * @param InputInterface $input
     */
    protected function validateDatabaseOption(InputInterface $input): void
    {
        if ($input->getOption('database') && ! in_array($input->getOption('database'), $drivers = ['mysql', 'mariadb', 'pgsql', 'sqlite', 'sqlsrv'])) {
            throw new InvalidArgumentException("Invalid database driver [{$input->getOption('database')}]. Valid options are: ".implode(', ', $drivers).'.');
        }
    }

    /**
     * Validate the starter kit stack input.
     *
     * @param InputInterface $input
     */
    protected function validateStackOption(InputInterface $input): void
    {
        if ($input->getOption('breeze')) {
            if (! in_array($input->getOption('stack'), $stacks = ['blade', 'livewire', 'livewire-functional', 'react', 'vue', 'api'])) {
                throw new InvalidArgumentException("Invalid Breeze stack [{$input->getOption('stack')}]. Valid options are: ".implode(', ', $stacks).'.');
            }

            return;
        }

        if ($input->getOption('jet')) {
            if (! in_array($input->getOption('stack'), $stacks = ['inertia', 'livewire'])) {
                throw new InvalidArgumentException("Invalid Jetstream stack [{$input->getOption('stack')}]. Valid options are: ".implode(', ', $stacks).'.');
            }
        }
    }

    /**
     * Determine the stack for Breeze.
     *
     * @param InputInterface $input
     * @return void
     */
    protected function promptForBreezeOptions(InputInterface $input): void
    {
        if (! $input->getOption('stack')) {
            $input->setOption('stack', select(
                label: 'Which Breeze stack would you like to install?',
                options: [
                    'blade' => 'Blade with Alpine',
                    'livewire' => 'Livewire (Volt Class API) with Alpine',
                    'livewire-functional' => 'Livewire (Volt Functional API) with Alpine',
                    'react' => 'React with Inertia',
                    'vue' => 'Vue with Inertia',
                    'api' => 'API only',
                ],
                default: 'blade',
            ));
        }

        if (in_array($input->getOption('stack'), ['react', 'vue']) && (! $input->getOption('dark') || ! $input->getOption('ssr'))) {
            collect(multiselect(
                label: 'Would you like any optional features?',
                options: [
                    'dark' => 'Dark mode',
                    'ssr' => 'Inertia SSR',
                    'typescript' => 'TypeScript',
                    'eslint' => 'ESLint with Prettier',
                ],
                default: array_filter([
                    $input->getOption('dark') ? 'dark' : null,
                    $input->getOption('ssr') ? 'ssr' : null,
                    $input->getOption('typescript') ? 'typescript' : null,
                    $input->getOption('eslint') ? 'eslint' : null,
                ]),
            ))->each(fn ($option) => $input->setOption($option, true));
        } elseif (in_array($input->getOption('stack'), ['blade', 'livewire', 'livewire-functional']) && ! $input->getOption('dark')) {
            $input->setOption('dark', confirm(
                label: 'Would you like dark mode support?',
                default: false,
            ));
        }
    }

    /**
     * Determine the stack for Jetstream.
     *
     * @param InputInterface $input
     * @return void
     */
    protected function promptForJetstreamOptions(InputInterface $input): void
    {
        if (! $input->getOption('stack')) {
            $input->setOption('stack', select(
                label: 'Which Jetstream stack would you like to install?',
                options: [
                    'livewire' => 'Livewire',
                    'inertia' => 'Vue with Inertia',
                ],
                default: 'livewire',
            ));
        }

        collect(multiselect(
            label: 'Would you like any optional features?',
            options: collect([
                'api' => 'API support',
                'dark' => 'Dark mode',
                'verification' => 'Email verification',
                'teams' => 'Team support',
            ])->when(
                $input->getOption('stack') === 'inertia',
                fn ($options) => $options->put('ssr', 'Inertia SSR')
            )->all(),
            default: array_filter([
                $input->getOption('api') ? 'api' : null,
                $input->getOption('dark') ? 'dark' : null,
                $input->getOption('teams') ? 'teams' : null,
                $input->getOption('verification') ? 'verification' : null,
                $input->getOption('stack') === 'inertia' && $input->getOption('ssr') ? 'ssr' : null,
            ]),
        ))->each(fn ($option) => $input->setOption($option, true));
    }

    /**
     * Install Pest into the application.
     *
     * @param string $directory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function installPest(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $composerBinary = $this->findComposer();

        $commands = [
            $composerBinary.' remove phpunit/phpunit --dev --no-update',
            $composerBinary.' require pestphp/pest pestphp/pest-plugin-laravel --no-update --dev',
            $composerBinary.' update',
            $this->phpBinary().' ./vendor/bin/pest --init',
        ];

        if ($input->getOption('react') || $input->getOption('vue') || $input->getOption('livewire')) {
            $commands[] = $composerBinary.' require pestphp/pest-plugin-drift --dev';
            $commands[] = $this->phpBinary().' ./vendor/bin/pest --drift';
            $commands[] = $composerBinary.' remove pestphp/pest-plugin-drift --dev';
        }

        $this->runCommands($commands, $input, $output, workingPath: $directory, env: [
            'PEST_NO_SUPPORT' => 'true',
        ]);

        $this->replaceFile(
            'pest/Feature.php',
            $directory.'/tests/Feature/ExampleTest.php',
        );

        $this->replaceFile(
            'pest/Unit.php',
            $directory.'/tests/Unit/ExampleTest.php',
        );

        if ($input->getOption('react') || $input->getOption('vue') || $input->getOption('livewire')) {
            $this->replaceInFile(
                './vendor/bin/phpunit',
                './vendor/bin/pest',
                $directory.'/.github/workflows/tests.yml',
            );
        }

        if (($input->getOption('react') || $input->getOption('vue') || $input->getOption('livewire')) && $input->getOption('phpunit')) {
            $this->deleteFile($directory.'/tests/Pest.php');
        }

        $this->commitChanges('Install Pest', $directory, $input, $output);
    }

    /**
     * Create a Git repository and commit the base Laravel skeleton.
     *
     * @param  string  $directory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function createRepository(string $directory, InputInterface $input, OutputInterface $output): void
    {
        $branch = $input->getOption('branch') ?: $this->defaultBranch();

        $commands = [
            'git init -q',
            'git add .',
            'git commit -q -m "Set up a fresh Laravel app"',
            "git branch -M $branch",
        ];

        $this->runCommands($commands, $input, $output, workingPath: $directory);
    }

    /**
     * Commit any changes in the current working directory.
     *
     * @param  string  $message
     * @param  string  $directory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function commitChanges(string $message, string $directory, InputInterface $input, OutputInterface $output): void
    {
        if (! $input->getOption('git') && $input->getOption('github') === false) {
            return;
        }

        $commands = [
            'git add .',
            "git commit -q -m \"$message\"",
        ];

        $this->runCommands($commands, $input, $output, workingPath: $directory);
    }

    /**
     * Create a GitHub repository and push the git log to it.
     *
     * @param  string  $name
     * @param  string  $directory
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function pushToGitHub(string $name, string $directory, InputInterface $input, OutputInterface $output): void
    {
        $process = new Process(['gh', 'auth', 'status']);
        $process->run();

        if (! $process->isSuccessful()) {
            $output->writeln('  <bg=yellow;fg=black> WARN </> Make sure the "gh" CLI tool is installed and that you\'re authenticated to GitHub. Skipping...'.PHP_EOL);

            return;
        }

        $name = $input->getOption('organization') ? $input->getOption('organization')."/$name" : $name;
        $flags = $input->getOption('github') ?: '--private';

        $commands = [
            "gh repo create $name --source=. --push $flags",
        ];

        $this->runCommands($commands, $input, $output, workingPath: $directory, env: ['GIT_TERMINAL_PROMPT' => 0]);
    }

    /**
     * Configure the Composer "dev" script.
     *
     * @return void
     * @throws JsonException
     */
    protected function configureComposerDevScript(): void
    {
        $this->composer->modify(function (array $content) {
            if (windows_os()) {
                $content['scripts']['dev'] = [
                    'Composer\\Config::disableProcessTimeout',
                    "npx concurrently -c \"#93c5fd,#c4b5fd,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1\" \"npm run dev\" --names='server,queue,vite'",
                ];
            }

            return $content;
        });
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param string $directory
     * @return void
     */
    protected function verifyApplicationDoesntExist(string $directory): void
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * Generate a valid APP_URL for the given application name.
     *
     * @param string $name
     * @return string
     */
    protected function generateAppUrl(string $name): string
    {
        $hostname = mb_strtolower($name).'.'.$this->getTld();

        return $this->canResolveHostname($hostname) ? 'http://'.$hostname : 'http://localhost';
    }

    /**
     * Determine if a starter kit is being used.
     *
     * @param InputInterface $input
     * @return bool
     */
    protected function usingLegacyStarterKit(InputInterface $input): bool
    {
        return $input->getOption('breeze') || $input->getOption('jet');
    }

    /**
     * Determine if a starter kit is being used.
     *
     * @param InputInterface $input
     * @return bool
     */
    protected function usingStarterKit(InputInterface $input): bool
    {
        return $input->getOption('react') || $input->getOption('vue') || $input->getOption('livewire') || $input->getOption('custom-starter');
    }

    /**
     * Get the TLD for the application.
     *
     * @return string
     */
    protected function getTld(): string
    {
        return $this->runOnValetOrHerd('tld') ?: 'test';
    }

    /**
     * Determine whether the given hostname is resolvable.
     *
     * @param string $hostname
     * @return bool
     */
    protected function canResolveHostname(string $hostname): bool
    {
        return gethostbyname($hostname.'.') !== $hostname.'.';
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
     * Get the version that should be downloaded.
     *
     * @param InputInterface $input
     * @return string
     */
    protected function getVersion(InputInterface $input): string
    {
        if ($input->getOption('dev')) {
            return 'dev-master';
        }

        return '';
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer(): string
    {
        return implode(' ', $this->composer->findComposer());
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
        if (! $output->isDecorated()) {
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

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
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
     * Replace the given file.
     *
     * @param  string  $replace
     * @param  string  $file
     * @return void
     */
    protected function replaceFile(string $replace, string $file): void
    {
        $stubs = dirname(__DIR__).'/stubs';

        file_put_contents(
            $file,
            file_get_contents("$stubs/$replace"),
        );
    }

    /**
     * Replace the given string in the given file.
     *
     * @param  string|array  $search
     * @param  string|array  $replace
     * @param  string  $file
     * @return void
     */
    protected function replaceInFile(string|array $search, string|array $replace, string $file): void
    {
        file_put_contents(
            $file,
            str_replace($search, $replace, file_get_contents($file))
        );
    }

    /**
     * Replace the given string in the given file using regular expressions.
     *
     * @param string $pattern
     * @param string $replace
     * @param string $file
     * @return void
     */
    protected function pregReplaceInFile(string $pattern, string $replace, string $file): void
    {
        file_put_contents(
            $file,
            preg_replace($pattern, $replace, file_get_contents($file))
        );
    }

    /**
     * Delete the given file.
     *
     * @param  string  $file
     * @return void
     */
    protected function deleteFile(string $file): void
    {
        unlink($file);
    }
}
