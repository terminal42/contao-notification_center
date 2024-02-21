<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition\Factory;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenDefinitionNameException;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

class ChainTokenDefinitionFactory implements TokenDefinitionFactoryInterface
{
    /**
     * @var array<TokenDefinitionFactoryInterface>
     */
    private array $factories = [];

    public function addFactory(TokenDefinitionFactoryInterface $factory): self
    {
        $this->factories[] = $factory;

        return $this;
    }

    /**
     * @throws InvalidTokenDefinitionNameException
     */
    public function create(string $definitionClass, string $tokenName, string $translationKey): TokenDefinitionInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($definitionClass)) {
                return $factory->create($definitionClass, $tokenName, $translationKey);
            }
        }

        throw InvalidTokenDefinitionNameException::becauseDoesNotExist($definitionClass);
    }

    public function supports(string $definitionClass): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($definitionClass)) {
                return true;
            }
        }

        return false;
    }
}
