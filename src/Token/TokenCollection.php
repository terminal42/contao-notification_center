<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Ramsey\Collection\AbstractCollection;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

/**
 * @extends AbstractCollection<Token>
 */
class TokenCollection extends AbstractCollection
{
    /**
     * @return array<string, mixed>
     */
    public function asKeyValue(bool $noStringable = false): array
    {
        $values = [];

        foreach ($this as $token) {
            $values[$token->getName()] = $noStringable ? (string) $token->getValue() : $token->getValue();
        }

        return $values;
    }

    public function serialize(): string
    {
        return json_encode($this->asKeyValue());
    }

    public function getType(): string
    {
        return Token::class;
    }

    /**
     * @param array<TokenDefinitionInterface> $tokenDefinitions
     */
    public static function fromRawAndDefinitions(array $rawTokens, array $tokenDefinitions): self
    {
        $collection = new self();

        foreach ($rawTokens as $rawTokenName => $rawTokenValue) {
            foreach ($tokenDefinitions as $definition) {
                if ($definition->matchesTokenName($rawTokenName)) {
                    $collection->add(Token::fromMixedValue($definition, $rawTokenName, $rawTokenValue));
                }
            }
        }

        return $collection;
    }
}
