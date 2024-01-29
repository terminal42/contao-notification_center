<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Exception\InvalidTokenException;
use Terminal42\NotificationCenterBundle\Token\TokenInterface;

interface TokenDefinitionInterface
{
    /**
     * Returns the name of the definition. It's used e.g. in the DCA files
     * to reference to the allowed token definitions.
     * E.g. "email".
     */
    public function getDefinitionName(): string;

    /**
     * Returns the token name for this definition which the user can use in the
     * interface using simple tokens.
     * E.g. "form_email".
     */
    public function getTokenName(): string;

    /**
     * Every token definition can have a description coming from the translation domain
     * "contao_nc_tokens". So if you return e.g. "form.foobar" here, the Notification Center
     * will search for the translation key "form.foobar" in the "contao_nc_tokens" domain.
     */
    public function getTranslationKey(): string;

    /**
     * Should return true if the token definition is responsible for a given token name.
     * Normally this is "$tokenName === $this->getTokenName()" but e.g. the WildcardToken
     * definition uses "*" as placeholder etc. Anything is doable in this method.
     */
    public function matchesTokenName(string $tokenName): bool;

    /**
     * This method is responsible for actually creating a concrete token (NOT the definition) from a
     * value. E.g. turning "foobar@example.com" into a StringToken instance.
     * By default, the tokenName is the same as defined on the token definition (also @see matchesTokenName() as this
     * is the default case). However, in cases like the WildcardToken where the definition itself uses a placeholder, the
     * concrete token name has to be provided. But really that is up to the token definition.
     *
     * @throws InvalidTokenException
     */
    public function createToken(mixed $value, string $tokenName = null): TokenInterface;
}
