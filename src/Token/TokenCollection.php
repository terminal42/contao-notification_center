<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Token>
 */
class TokenCollection extends AbstractCollection
{
    public static function fromSerializedArray(array $data): self
    {
        $tokens = [];

        foreach ($data as $token) {
            if (!isset($token['class']) || !class_exists($token['class']) || !is_a($token['class'], Token::class, true)) {
                continue;
            }

            $tokens[] = $token['class']::fromArray($token['data'] ?? []);
        }

        return new self($tokens);
    }

    /**
     * Provides a fluent interface alternative to add() with a type hint.
     */
    public function addToken(Token $token): self
    {
        $this->add($token);

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function forSimpleTokenParser(): array
    {
        $data = [];

        /** @var Token $token */
        foreach ($this as $token) {
            $data[$token->getName()] = $token->getParserValue();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toKeyValue(): array
    {
        $data = [];

        /** @var Token $token */
        foreach ($this as $token) {
            $data[$token->getName()] = $token->getValue();
        }

        return $data;
    }

    public function getByName(string $name): Token|null
    {
        /** @var Token $token */
        foreach ($this as $token) {
            if ($token->getName() === $name) {
                return $token;
            }
        }

        return null;
    }

    public function has(string $name): bool
    {
        return null !== $this->getByName($name);
    }

    /**
     * @return array<array{class: string, data: array}>
     */
    public function toSerializableArray(): array
    {
        $data = [];

        /** @var Token $token */
        foreach ($this as $token) {
            $data[] = [
                'class' => $token::class,
                'data' => $token->toArray(),
            ];
        }

        return $data;
    }

    public function getType(): string
    {
        return Token::class;
    }
}
