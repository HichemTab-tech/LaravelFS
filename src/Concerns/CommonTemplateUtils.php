<?php

namespace HichemTabTech\LaravelFS\Console\Concerns;

use Symfony\Component\Console\Input\InputInterface;
use function Laravel\Prompts\text;

trait CommonTemplateUtils
{
    protected function ensureTemplateNameArgument(InputInterface $input): void
    {
        $templatesData = $this->getSavedTemplates(true);
        $templates = $templatesData['templates'];
        $input->setArgument('template-name', text(
            label: 'What is the name this template',
            placeholder: count($templates) == 0 ? 'E.g. template1, or-any-name-u-want' : ('E.g. '.implode(', ', array_slice(array_keys($templates), 0, 3)).(count($templates) > 3 ? ', ...' : '')),
            required: 'The template name is required.',
            hint: 'This name is the key of the template you are searching for.',
        ));
    }
}