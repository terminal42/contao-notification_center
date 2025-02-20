<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Token>
 */
class TokenCollection extends AbstractCollection
{
    /**
     * @param array<array{class: string, data: array{name: string, value: mixed, parserValue: string}}> $data
     */
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

    public function replaceToken(Token $token): self
    {
        $existing = $this->getByName($token->getName());

        if (null !== $existing) {
            $this->remove($existing);
        }

        $this->addToken($token);

        return $this;
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
        $normalized = [];

        /** @var Token $token */
        foreach ($this as $token) {
            $data[$token->getName()] = $token->getParserValue();

            // Replace everything that's not allowed from the beginning of a string (PHP
            // variables cannot start with numbers for example)
            $tokenName = preg_replace_callback(
                '/^[^a-zA-Z_\x7f-\xff]*/',
                static function (array $matches) {
                    if ($matches[0]) {
                        return str_repeat('_', \strlen($matches[0]));
                    }
                },
                $token->getName(),
            );

            // Then also replace all the rest (after that, numbers are allowed)
            $tokenName = preg_replace_callback(
                '/[^a-zA-Z0-9_\x7f-\xff]/',
                static function (array $matches) {
                    if ($matches[0]) {
                        return str_repeat('_', \strlen($matches[0]));
                    }
                },
                (string) $tokenName,
            );

            if ($tokenName !== $token->getName()) {
                $normalized[$tokenName] = $token->getParserValue();
            }
        }

        foreach ($normalized as $tokenName => $value) {
            if (!isset($data[$tokenName])) {
                $data[$tokenName] = $value;
            }
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
     * @return array<array{class: string, data: array{name: string, value: mixed, parserValue: string}}>
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

    public function hasAll(string ...$names): bool
    {
        foreach ($names as $name) {
            if (!$this->has($name)) {
                return false;
            }
        }

        return true;
    }

    public function hasAny(string ...$names): bool
    {
        foreach ($names as $name) {
            if ($this->has($name)) {
                return true;
            }
        }

        return false;
    }
}
