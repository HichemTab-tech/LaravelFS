<?php

namespace HichemTabTech\LaravelFS\Console;

use Symfony\Component\Console\Input\InputArgument;

class NewTemplateCommand extends NewCommand
{
    public function __construct()
    {
        parent::__construct(true);
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setName('template:new')
            ->setDescription('Create and save a custom starter template')
            ->setHelp(
                'This command mimics the "new" command prompts to collect all configuration options for a Laravel project starter kit. ' .
                'Instead of creating a new project, it assembles your chosen options into a command that you can use later, ' .
                'letting you quickly re-create your custom starter template later.'
            )
            ->addArgument('template-name', InputArgument::REQUIRED)
            ->addArgument(
                'template-description',
                InputArgument::OPTIONAL,
                'This is a description for the template that will be used to describe the template.'
            );
    }
}
