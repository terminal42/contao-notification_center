<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition\Factory;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenDefinitionNameException;
use Terminal42\NotificationCenterBundle\Token\Definition\AbstractTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailToken;
use Terminal42\NotificationCenterBundle\Token\Definition\FileToken;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

class CoreTokenDefinitionFactory implements TokenDefinitionFactoryInterface
{
    private const MAPPER = [
        EmailToken::DEFINITION_NAME => EmailToken::class,
        FileToken::DEFINITION_NAME => FileToken::class,
        HtmlToken::DEFINITION_NAME => HtmlToken::class,
        TextToken::DEFINITION_NAME => TextToken::class,
        WildcardToken::DEFINITION_NAME => WildcardToken::class,
    ];

    public function supports(string $definitionName): bool
    {
        return \array_key_exists($definitionName, self::MAPPER);
    }

    /**
     * @throws InvalidTokenDefinitionNameException
     */
    public function create(string $definitionName, string $tokenName, string $translationKey): TokenDefinitionInterface
    {
        $class = self::MAPPER[$definitionName] ?? null;

        if (null === $class) {
            throw InvalidTokenDefinitionNameException::becauseDoesNotExist($definitionName);
        }

        if (!is_a($class, AbstractTokenDefinition::class, true)) {
            throw InvalidTokenDefinitionNameException::becauseDoesNotExist($definitionName);
        }

        return $class::createFromNameAndTranslationKey($tokenName, $translationKey);
    }
}
