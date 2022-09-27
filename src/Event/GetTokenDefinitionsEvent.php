<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\MessageType\MessageTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

class GetTokenDefinitionsEvent extends Event
{
    /**
     * @var array<TokenDefinitionInterface>
     */
    private array $tokenDefinitions = [];

    /**
     * @param array<TokenDefinitionInterface> $tokenDefinitions
     */
    public function __construct(private MessageTypeInterface $messageType, array $tokenDefinitions = [])
    {
        foreach ($tokenDefinitions as $token) {
            $this->addTokenDefinition($token);
        }
    }

    public function getMessageType(): MessageTypeInterface
    {
        return $this->messageType;
    }

    public function addTokenDefinition(TokenDefinitionInterface $token): self
    {
        $this->tokenDefinitions[$token->getName()] = $token;

        return $this;
    }

    /**
     * @param array<string> $tokenDefinitionTypes
     *
     * @return array<string, TokenDefinitionInterface>
     */
    public function getTokenDefinitions(array $tokenDefinitionTypes = []): array
    {
        if ([] === $tokenDefinitionTypes) {
            return $this->tokenDefinitions;
        }

        $definitions = [];

        foreach ($this->tokenDefinitions as $definition) {
            if (\in_array($definition::class, $tokenDefinitionTypes, true)) {
                $definitions[$definition->getName()] = $definition;
            }
        }

        return $definitions;
    }
}
