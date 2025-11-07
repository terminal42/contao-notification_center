<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType;

use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

class FormGeneratorNotificationType implements NotificationTypeInterface
{
    public const NAME = 'core_form';

    public function __construct(private readonly TokenDefinitionFactoryInterface $factory)
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            $this->factory->create(AnythingTokenDefinition::class, 'form_*', 'form.form_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'formconfig_*', 'form.formconfig_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'formlabel_*', 'form.formlabel_*'),
            $this->factory->create(TextTokenDefinition::class, 'raw_data', 'form.raw_data'),
            $this->factory->create(TextTokenDefinition::class, 'raw_data_filled', 'form.raw_data_filled'),
            $this->factory->create(HtmlTokenDefinition::class, 'raw_data', 'form.raw_data'),
            $this->factory->create(HtmlTokenDefinition::class, 'raw_data_filled', 'form.raw_data_filled'),
        ];
    }
}
