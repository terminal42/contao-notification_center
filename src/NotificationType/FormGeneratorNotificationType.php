<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType;

use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

class FormGeneratorNotificationType implements NotificationTypeInterface
{
    public const NAME = 'core_form';

    public function __construct(private TokenDefinitionFactoryInterface $factory)
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'form_*', 'form.form_*'),
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'formconfig_*', 'form.formconfig_*'),
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'formlabel_*', 'form.formlabel_*'),
            $this->factory->create(TextToken::DEFINITION_NAME, 'raw_data', 'form.raw_data'),
            $this->factory->create(TextToken::DEFINITION_NAME, 'raw_data_filled', 'form.raw_data_filled'),
        ];
    }
}
