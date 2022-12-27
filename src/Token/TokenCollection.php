<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<TokenInterface>
 */
class TokenCollection extends AbstractCollection
{
    public static function fromSerialized(string $serialized): self
    {
        $tokens = [];
        $data = json_decode($serialized, true);

        if (!\is_array($data)) {
            return new self();
        }

        foreach ($data as $class => $tokenData) {
            if (!class_exists($class) || !is_a($class, TokenInterface::class, true)) {
                continue;
            }

            $tokens[] = $class::fromSerialized($tokenData);
        }

        return new self($tokens);
    }

    /**
     * @return array<string, string>
     */
    public function forSimpleTokenParser(): array
    {
        $data = [];

        /** @var TokenInterface $token */
        foreach ($this as $token) {
            $data[$token->getName()] = $token->getParserValue();
        }

        return $data;
    }

    public function serialize(): string
    {
        $data = [];

        /** @var TokenInterface $token */
        foreach ($this as $token) {
            $data[\get_class($token)] = $token->serialize();
        }

        return json_encode($data);
    }

    public function getType(): string
    {
        return TokenInterface::class;
    }
}
