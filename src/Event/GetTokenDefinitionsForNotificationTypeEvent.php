<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

class GetTokenDefinitionsForNotificationTypeEvent extends Event
{
    /**
     * @var array<TokenDefinitionInterface>
     */
    private array $tokenDefinitions = [];

    /**
     * @param array<TokenDefinitionInterface> $tokenDefinitions
     */
    public function __construct(private NotificationTypeInterface $notificationType, array $tokenDefinitions = [])
    {
        foreach ($tokenDefinitions as $token) {
            $this->addTokenDefinition($token);
        }
    }

    public function getNotificationType(): NotificationTypeInterface
    {
        return $this->notificationType;
    }

    public function addTokenDefinition(TokenDefinitionInterface $token): self
    {
        $this->tokenDefinitions[$token->getTokenName()] = $token;

        return $this;
    }

    public function removeTokenDefinition(TokenDefinitionInterface $token): self
    {
        unset($this->tokenDefinitions[$token->getTokenName()]);

        return $this;
    }

    /**
     * @param array<class-string<TokenDefinitionInterface>> $tokenDefinitionClasses
     *
     * @return array<string, TokenDefinitionInterface>
     */
    public function getTokenDefinitions(array $tokenDefinitionClasses = []): array
    {
        if ([] === $tokenDefinitionClasses) {
            return $this->tokenDefinitions;
        }

        $definitions = [];

        foreach ($this->tokenDefinitions as $definition) {
            if (\in_array($definition::class, $tokenDefinitionClasses, true)) {
                $definitions[$definition->getTokenName()] = $definition;
            }
        }

        return $definitions;
    }
}
