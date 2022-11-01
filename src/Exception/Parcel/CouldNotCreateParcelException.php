<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception\Parcel;

class CouldNotCreateParcelException extends \InvalidArgumentException
{
    public static function becauseOfNonExistentMessage(int $messageId, int $code = 0, \Throwable $previous = null): self
    {
        return new self(sprintf('The message ID "%s" does not exist.', $messageId), $code, $previous);
    }
}
