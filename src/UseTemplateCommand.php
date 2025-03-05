<?php

namespace HichemTabTech\LaravelFS\Console;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\error;
use function Laravel\Prompts\text;

class UseTemplateCommand extends Command
{
    use Concerns\CommandsUtils, Concerns\ConfiguresPrompts, Concerns\CommonTemplateUtils;
    protected function configure(): void
    {
        $this->setName('use')
            ->setDescription('Use a saved template to create a new Laravel project')
            ->setHelp('This command uses a saved template to create a new Laravel project.')
            ->addArgument('template-name', InputArgument::REQUIRED, 'The name of the template to use')
            ->addArgument('project-name', InputArgument::REQUIRED)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        parent::interact($input, $output);

        $this->configurePrompts($input, $output);

        $output->write(PHP_EOL.'  <fg=blue> _                               _
  | |                             | |
  | |     __ _ _ __ __ ___   _____| |
  | |    / _` |  __/ _` \ \ / / _ \ |
  | |___| (_| | | | (_| |\ V /  __/ |
  |______\__,_|_|  \__,_| \_/ \___|_|</>'.PHP_EOL.PHP_EOL);

        if (!$input->getArgument('template-name')) {
            $this->ensureTemplateNameArgument($input);
        }

        if (!$input->getArgument('project-name')) {
            $input->setArgument('project-name', text(
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
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->useTemplate($input, $output);
        return Command::SUCCESS;
    }

    private function useTemplate(InputInterface $input, OutputInterface $output): void
    {
        $templatesData = $this->getSavedTemplates();
        $templates = $templatesData['templates'];
        if (empty($templates)) {
            return;
        }

        $templateName = $input->getArgument('template-name');

        if (!isset($templates[$templateName])) {
            error("Template '$templateName' not found.");
            return;
        }

        $template = $templates[$templateName];
        $template['command'] = str_replace('<project-name>', $input->getArgument('project-name'), $template['command']);

        $this->runCommands([$template['command']], $input, $output);
    }
}