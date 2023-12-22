<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

interface TokenInterface
{
    /**
     * Returns the name of the token. E.g. "form_email".
     */
    public function getName(): string;

    public function getValue(): mixed;

    /**
     * Returns the value passed on to the simple token parser.
     * Thus, it has to be a string. Usually, the same value as in toArray()
     * but e.g. a multidimensional array could have a comma-separated representation
     * here for human readability but in toArray() and fromArray() it's likely
     * using a JSON representation.
     */
    public function getParserValue(): string;

    public function toArray(): array;

    public static function fromArray(array $data): self;
}
