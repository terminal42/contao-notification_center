<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition\Factory;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenDefinitionNameException;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

interface TokenDefinitionFactoryInterface
{
    public function supports(string $definitionName): bool;

    /**
     * @throws InvalidTokenDefinitionNameException
     */
    public function create(string $definitionName, string $tokenName, string $translationKey): TokenDefinitionInterface;
}
