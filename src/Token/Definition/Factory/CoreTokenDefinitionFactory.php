<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition\Factory;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenDefinitionNameException;
use Terminal42\NotificationCenterBundle\Token\Definition\AbstractTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

class CoreTokenDefinitionFactory implements TokenDefinitionFactoryInterface
{
    public function supports(string $definitionClass): bool
    {
        return is_a($definitionClass, AbstractTokenDefinition::class, true);
    }

    /**
     * @throws InvalidTokenDefinitionNameException
     */
    public function create(string $definitionClass, string $tokenName, string $translationKey): TokenDefinitionInterface
    {
        if (!is_a($definitionClass, AbstractTokenDefinition::class, true)) {
            throw InvalidTokenDefinitionNameException::becauseDoesNotExist($definitionClass);
        }

        return new $definitionClass($tokenName, $translationKey);
    }
}
