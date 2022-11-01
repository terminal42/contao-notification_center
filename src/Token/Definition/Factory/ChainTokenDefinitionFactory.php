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
     * @return array<string,class-string<TokenDefinitionInterface>>
     */
    public function all(): array
    {
        $all = [];

        foreach ($this->factories as $factory) {
            $all = array_merge($all, $factory->all());
        }

        return $all;
    }

    /**
     * @return class-string<TokenDefinitionInterface>|null
     */
    public function getDefinitionByName(string $name): string|null
    {
        foreach ($this->factories as $factory) {
            if (null !== ($definition = $factory->getDefinitionByName($name))) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * @throws InvalidTokenDefinitionNameException
     */
    public function create(string $definitionName, string $tokenName, string $translationKey): TokenDefinitionInterface
    {
        $definition = $this->getDefinitionByName($definitionName);

        if (null === $definition) {
            throw InvalidTokenDefinitionNameException::becauseDoesNotExist($definitionName);
        }

        return $definition::create($tokenName, $translationKey);
    }
}
