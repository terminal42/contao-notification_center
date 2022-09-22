<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

interface TokenDefinitionInterface
{
    public function getName(): string;

    public function getTranslationKey(): string;

    public function matchesTokenName(string $tokenName): bool;
}
