<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition\Factory;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenDefinitionNameException;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailToken;
use Terminal42\NotificationCenterBundle\Token\Definition\FileToken;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

class CoreTokenDefinitionFactory implements TokenDefinitionFactoryInterface
{
    /**
     * @return array<string,class-string<TokenDefinitionInterface>>
     */
    public function all(): array
    {
        return [
            EmailToken::DEFINITION_NAME => EmailToken::class,
            FileToken::DEFINITION_NAME => FileToken::class,
            HtmlToken::DEFINITION_NAME => HtmlToken::class,
            TextToken::DEFINITION_NAME => TextToken::class,
            WildcardToken::DEFINITION_NAME => WildcardToken::class,
        ];
    }

    /**
     * @return class-string<TokenDefinitionInterface>|null
     */
    public function getDefinitionByName(string $name): string|null
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * @throws InvalidTokenDefinitionNameException
     */
    public function create(string $definitionName, string $tokenName, string $translationKey): TokenDefinitionInterface
    {
        $definition = $this->getDefinitionByName($definitionName);

        if (null === $definition) {
            throw InvalidTokenDefinitionNameException::becauseDoesNotExist($definitionName);
        }

        return $definition::create($tokenName, $translationKey);
    }
}
