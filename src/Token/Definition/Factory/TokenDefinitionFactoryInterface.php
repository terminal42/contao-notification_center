<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition\Factory;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenDefinitionNameException;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

interface TokenDefinitionFactoryInterface
{
    /**
     * @return array<string,class-string<TokenDefinitionInterface>>
     */
    public function all(): array;

    /**
     * @return class-string<TokenDefinitionInterface>|null
     */
    public function getDefinitionByName(string $name): string|null;

    /**
     * @throws InvalidTokenDefinitionNameException
     */
    public function create(string $definitionName, string $tokenName, string $translationKey): TokenDefinitionInterface;
}
