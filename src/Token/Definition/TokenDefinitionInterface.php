<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

interface TokenDefinitionInterface
{
    public function getDefinitionName(): string;

    public function getTokenName(): string;

    public function getTranslationKey(): string;

    public function matchesTokenName(string $tokenName): bool;

    public static function create(string $tokenName, string $translationKey): self;
}
