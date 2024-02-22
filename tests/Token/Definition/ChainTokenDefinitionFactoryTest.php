<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token\Definition;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Parcel\StampCollection;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\ChainTokenDefinitionFactory;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\CoreTokenDefinitionFactory;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;
use Terminal42\NotificationCenterBundle\Token\Token;

class ChainTokenDefinitionFactoryTest extends TestCase
{
    public function testFactoryWorksAsExpected(): void
    {
        $factory = new ChainTokenDefinitionFactory();

        $this->assertFalse($factory->supports(EmailTokenDefinition::class));

        $factory->addFactory(new CoreTokenDefinitionFactory());

        $this->assertTrue($factory->supports(EmailTokenDefinition::class));

        $definition = $factory->create(EmailTokenDefinition::class, 'form_email', 'foobar');

        $this->assertInstanceOf(EmailTokenDefinition::class, $definition);
        $this->assertSame('form_email', $definition->getTokenName());
        $this->assertSame('foobar', $definition->getTranslationKey());

        $this->assertFalse($factory->supports('iban-token-definition'));

        $exampleDefinition = $this->createExampleTokenDefinition('form_iban', 'foobar', 'iban-validator-service-id');
        $factory->addFactory($this->createExampleFactoryWithDependencyInjection($exampleDefinition));

        $this->assertTrue($factory->supports($exampleDefinition::class));

        $definition = $factory->create($exampleDefinition::class, 'form_iban', 'foobar');

        $this->assertSame('form_iban', $definition->getTokenName());
        $this->assertSame('foobar', $definition->getTranslationKey());

        $token = $definition->createToken('form_iban', 'CH...');
        $this->assertSame('i-would-call-service: iban-validator-service-id and return value: CH...', $token->getValue());
        $this->assertSame('parser-value', $token->getParserValue());
    }

    private function createExampleTokenDefinition(string $tokenName, string $translationKey, string $serviceDummy): TokenDefinitionInterface
    {
        return new class($tokenName, $translationKey, $serviceDummy) implements TokenDefinitionInterface {
            final public function __construct(
                private readonly string $tokenName,
                private readonly string $translationKey,
                private readonly string $serviceDummy,
            ) {
            }

            public function getTranslationKey(): string
            {
                return $this->translationKey;
            }

            public function matches(string $tokenName, mixed $value): bool
            {
                return true;
            }

            public function createToken(string $tokenName, mixed $value, StampCollection|null $stamps = null): Token
            {
                return new Token($tokenName, 'i-would-call-service: '.$this->serviceDummy.' and return value: '.$value, 'parser-value');
            }

            public function getTokenName(): string
            {
                return $this->tokenName;
            }
        };
    }

    private function createExampleFactoryWithDependencyInjection(TokenDefinitionInterface $definition): TokenDefinitionFactoryInterface
    {
        return new class($definition) implements TokenDefinitionFactoryInterface {
            public function __construct(private readonly TokenDefinitionInterface $definition)
            {
            }

            public function supports(string $definitionClass): bool
            {
                return $this->definition::class === $definitionClass;
            }

            public function create(string $definitionClass, string $tokenName, string $translationKey): TokenDefinitionInterface
            {
                return $this->definition;
            }
        };
    }
}
