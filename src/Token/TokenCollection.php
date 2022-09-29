<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<TokenInterface>
 */
class TokenCollection extends AbstractCollection
{
    /**
     * @return array<string, mixed>
     */
    public function asRawKeyValue(): array
    {
        $values = [];

        foreach ($this as $token) {
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

    public function getType(): string
    {
        return TokenInterface::class;
    }
}
