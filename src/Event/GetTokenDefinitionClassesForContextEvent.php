<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

class GetTokenDefinitionClassesForContextEvent extends Event
{
    /**
     * @var array<class-string<TokenDefinitionInterface>, bool>
     */
    private array $tokenDefinitionClasses = [];

    /**
     * @param array<class-string<TokenDefinitionInterface>> $tokenDefinitionClasses
     */
    public function __construct(
        private readonly string $context,
        array $tokenDefinitionClasses = [],
    ) {
        foreach ($tokenDefinitionClasses as $class) {
            $this->addTokenDefinitionClass($class);
        }
    }

    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param class-string<TokenDefinitionInterface> $class
     */
    public function addTokenDefinitionClass(string $class): self
    {
        $this->tokenDefinitionClasses[$class] = true;

        return $this;
    }

    /**
     * @param class-string<TokenDefinitionInterface> $class
     */
    public function removeTokenDefinitionClass(string $class): self
    {
        unset($this->tokenDefinitionClasses[$class]);

        return $this;
    }

    /**
     * @return array<class-string<TokenDefinitionInterface>>
     */
    public function getTokenDefinitionClasses(): array
    {
        return array_keys($this->tokenDefinitionClasses);
    }
}
