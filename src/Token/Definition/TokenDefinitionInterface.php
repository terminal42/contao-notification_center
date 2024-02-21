<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

use Terminal42\NotificationCenterBundle\Parcel\StampCollection;
use Terminal42\NotificationCenterBundle\Token\Token;

interface TokenDefinitionInterface
{
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
     * Should return true if the token definition is responsible for a given token name and value.
     * Normally this is "$tokenName === $this->getTokenName()" or if the token name ends with "_*" a regex matching
     * on this placeholder. But anything is doable in this method. Maybe your definition is responsible for only tokens
     * with a given prefix?
     */
    public function matches(string $tokenName, mixed $value): bool;

    /**
     * This method is responsible for actually creating a concrete token (NOT the definition) from a
     * name and a value. E.g. turning "form_email" and "foobar@example.com" into a Token instance.
     * Use this method to e.g. convert object values into your own parsed variant of it.
     */
    public function createToken(string $tokenName, mixed $value, StampCollection $stamps = null): Token;
}
