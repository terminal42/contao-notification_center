<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

class GetTokenDefinitionsEvent extends Event
{
    /**
     * @var array<TokenDefinitionInterface>
     */
    private array $tokenDefinitions = [];

    public function __construct(private string $notificationType, array $tokenDefinitions = [])
    {
        foreach ($tokenDefinitions as $token) {
            $this->addTokenDefinition($token);
        }
    }

    public function getNotificationType(): string
    {
        return $this->notificationType;
    }

    public function addTokenDefinition(TokenDefinitionInterface $token): self
    {
        $this->tokenDefinitions[$token->getName()] = $token;

        return $this;
    }

    public function getTokenDefinitions(array $tokenDefinitionTypes = []): array
    {
        if ([] === $tokenDefinitionTypes) {
            return $this->tokenDefinitions;
        }

        $definitions = [];

        foreach ($this->tokenDefinitions as $definition) {
            if (\in_array(\get_class($definition), $tokenDefinitionTypes, true)) {
                $definitions[$definition->getName()] = $definition;
            }
        }

        return $definitions;
    }
}
