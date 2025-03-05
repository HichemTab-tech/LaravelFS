<?php

namespace HichemTabTech\LaravelFS\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\table;
use function Laravel\Prompts\error;

class ShowTemplatesCommand extends Command
{
    use Concerns\CommandsUtils;
    protected function configure(): void
    {
        $this->setName('template:show')
            ->setDescription('Show all saved templates')
            ->setHelp('This command shows all saved templates that you can use to create a new Laravel project.')
            ->addArgument('template', InputArgument::OPTIONAL, 'Show a specific template')
            ->setAliases(['templates']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->showTemplates($input);
        return Command::SUCCESS;
    }


    private function showTemplates(InputInterface $input): void
    {
        $templatesData = $this->getSavedTemplates();
        $templates = $templatesData['templates'];
        if (empty($templates)) {
            return;
        }

        $path = $templatesData['path'];
        intro("Templates are saved in $path");

        if ($template = $input->getArgument('template')) {
            if (!isset($templates[$template])) {
                error("Template '$template' not found.");
                return;
            }

            // Display the template
            table(
                ['Template Name', 'Description', 'Command'],
                [$this->formatTemplates($template, $templates[$template])]
            );
        }
        else {
            // Format and display templates using Laravel Prompts table
            table(
                ['Template Name', 'Description', 'Command'],
                array_map(fn($name, $template) => $this->formatTemplates($name, $template), array_keys($templates), $templates)
            );
        }

        info('Use a template by calling <fg=cyan>`laravelfs use <template-name> <project-name>`<'.'/'.'>');
    }

    private function formatTemplates($name, $data): array
    {
        $maxLength = 50;
        $description = $data['description'] ?? 'No description';
        $command = $data['command'] ?? '';

        return [
            $name,
            strlen($description) > $maxLength ? substr($description, 0, $maxLength) . '...' : $description,
            strlen($command) > $maxLength ? substr($command, 0, $maxLength) . '...' : $command,
        ];
    }
}