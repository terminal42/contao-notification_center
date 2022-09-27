<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Terminal42\NotificationCenterBundle\MessageType\MessageTypeInterface;

class TokenCollection
{
    /**
     * @var array<TokenInterface>
     */
    private $tokens = [];

    /**
     * @param array<TokenInterface> $tokens
     */
    public function __construct(private MessageTypeInterface $messageType, array $tokens = [])
    {
        foreach ($tokens as $token) {
            $this->add($token);
        }
    }

    public function getMessageType(): MessageTypeInterface
    {
        return $this->messageType;
    }

    /**
     * @return array<TokenInterface>
     */
    public function all(): array
    {
        return $this->tokens;
    }

    public function add(TokenInterface $token): self
    {
        $this->tokens[] = $token;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function asRawKeyValue(): array
    {
        $values = [];

        foreach ($this->all() as $token) {
            $values[$token->getName()] = $token->getValue();
        }

        return $values;
    }

    /**
     * @return array<string, string>
     */
    public function asRawKeyValueWithStringsOnly(): array
    {
        $values = [];

        foreach ($this->asRawKeyValue() as $k => $value) {
            if (\is_string($value)) {
                $values[$k] = $value;
            }
        }

        return $values;
    }

    public function serialize(): string
    {
        return json_encode($this->asRawKeyValue());
    }
}
