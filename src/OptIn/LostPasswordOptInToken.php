<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\OptIn;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\OptIn\OptInTokenInterface;

class LostPasswordOptInToken implements OptInTokenInterface
{
    public function __construct(private OptInTokenInterface $originalToken, private ContaoFramework $framework)
    {
    }

    public function getIdentifier(): string
    {
        return $this->originalToken->getIdentifier();
    }

    public function getEmail(): string
    {
        return $this->originalToken->getEmail();
    }

    public function isValid(): bool
    {
        return $this->originalToken->isValid();
    }

    public function confirm(): void
    {
        $this->originalToken->confirm();
    }

    public function isConfirmed(): bool
    {
        return $this->originalToken->isConfirmed();
    }

    public function send(string $subject = null, string $text = null): void
    {
        dd($subject, $text);
        // TODO: Implement send() method.
    }

    public function hasBeenSent(): bool
    {
        return $this->originalToken->hasBeenSent();
    }

    public function getRelatedRecords(): array
    {
        return $this->originalToken->getRelatedRecords();
    }
}
