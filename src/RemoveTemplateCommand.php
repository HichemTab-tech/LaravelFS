<?php

namespace HichemTabTech\LaravelFS\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;

class RemoveTemplateCommand extends Command
{
    use Concerns\CommandsUtils, Concerns\ConfiguresPrompts, Concerns\CommonTemplateUtils;

    protected function configure(): void
    {
        $this->setName('template:remove')
            ->setDescription('remove a saved template')
            ->setHelp('This command removes a saved template that you no longer need.')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Remove all saved templates')
            ->addArgument('template-name', InputArgument::REQUIRED, 'The name of the template to remove');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        parent::interact($input, $output);

        $this->configurePrompts($input, $output);

        $output->write(PHP_EOL . '  <fg=blue> _                               _
  | |                             | |
  | |     __ _ _ __ __ ___   _____| |
  | |    / _` |  __/ _` \ \ / / _ \ |
  | |___| (_| | | | (_| |\ V /  __/ |
  |______\__,_|_|  \__,_| \_/ \___|_|</>' . PHP_EOL . PHP_EOL);

        if (!$input->getArgument('template-name')) {
            if (!$input->getOption('all')) {
                $this->ensureTemplateNameArgument($input);
            } else {
                $input->setArgument('template-name', confirm(
                    label: 'Are you sure you want to remove all saved templates?',
                    default: false,
                    hint: 'This action is irreversible.',
                ) ? '/all/' : null);
            }
        }
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $templatesData = $this->getSavedTemplates();
        $templates = $templatesData['templates'];
        if (empty($templates)) {
            $this->info('No saved templates found.');
            return Command::SUCCESS;
        }

        if ($input->getArgument('template-name') == '/all/') {
            $templateToRemove = null;
        } elseif (!$input->getArgument('template-name')) {
            intro('Operation cancelled.');
            return Command::SUCCESS;
        } else {
            $templateName = $input->getArgument('template-name');

            if (!isset($templates[$templateName])) {
                error("Template '$templateName' not found.");
                return Command::INVALID;
            }

            $templateToRemove = $templateName;
        }
        intro("Removing a saved template...");

        if ($templateToRemove) {
            $done = $this->removeTemplates($templatesData['path'], $templatesData, $templateToRemove);
        } else {
            $done = $this->removeTemplates($templatesData['path'], $templatesData);
        }

        if ($done) {
            $this->info($templateToRemove ? "Template '$templateToRemove' removed successfully." : 'All saved templates removed successfully.');
            return Command::SUCCESS;
        }

        error($templateToRemove ? "Failed to remove template '$templateToRemove'." : 'Failed to remove all saved templates.');
        return Command::FAILURE;
    }

    private function removeTemplates(string $path, array $templatesData, string|null $templateToRemove = null): bool
    {
        if (!$templateToRemove) {
            $templatesConfig = ['templates' => []];
        } else {
            unset($templatesData['templates'][$templateToRemove]);
            $templatesConfig = ['templates' => $templatesData['templates']];
        }
        return file_put_contents($path, json_encode($templatesConfig, JSON_PRETTY_PRINT)) !== false;
    }
}