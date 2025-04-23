<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Receipt;

final class AsynchronousReceipt
{
    private \Throwable|null $exception = null;

    private function __construct(
        private readonly string $identifier,
        private readonly bool $wasDelivered,
    ) {
        $length = \strlen($this->identifier);
        if ($length < 1 || $length > 64) {
            throw new \InvalidArgumentException('The identifier length must be between 1 and 64 characters.');
        }
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function wasDelivered(): bool
    {
        return $this->wasDelivered;
    }

    public function getException(): \Throwable|null
    {
        return $this->exception;
    }

    public static function createForSuccessfulDelivery(string $identifier): self
    {
        return new self($identifier, true);
    }

    public static function createForUnsuccessfulDelivery(string $identifier, \Throwable $exception): self
    {
        $receipt = new self($identifier, false);
        $receipt->exception = $exception;

        return $receipt;
    }
}
