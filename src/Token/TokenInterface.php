<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

interface TokenInterface
{
    /**
     * Contains the original token definition.
     * This is different from getToken() because it contains
     * e.g. a wildcard token "form_*".
     */
    public function getDefinition(): TokenDefinitionInterface;

    /**
     * This contains the used token which matched the token definition.
     * Might be e.g. "form_email" here.
     */
    public function getName(): string;

    public function getValue(): mixed;
}
