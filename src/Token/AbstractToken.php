<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

abstract class AbstractToken implements TokenInterface
{
    public function __construct(protected mixed $tokenData, private string $tokenName, private string $tokenDefinitionName)
    {
    }

    public function getName(): string
    {
        return $this->tokenName;
    }

    public function getDefinitionName(): string
    {
        return $this->tokenDefinitionName;
    }

    abstract public function getParserValue(): string;

    public function toArray(): array
    {
        return [
            'raw' => $this->tokenData,
            'name' => $this->getName(),
            'definition' => $this->getDefinitionName(),
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static($data['raw'], $data['name'], $data['definition']);
    }
}
