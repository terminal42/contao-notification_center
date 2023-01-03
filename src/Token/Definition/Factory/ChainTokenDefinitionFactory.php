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
    public function create(string $definitionName, string $tokenName, string $translationKey): TokenDefinitionInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($definitionName)) {
                return $factory->create($definitionName, $tokenName, $translationKey);
            }
        }

        throw InvalidTokenDefinitionNameException::becauseDoesNotExist($definitionName);
    }

    public function supports(string $definitionName): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($definitionName)) {
                return true;
            }
        }

        return false;
    }
}
