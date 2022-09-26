<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Symfony\Component\Mime\Email;

class MailerParcel extends AbstractParcel
{
    public function __construct(public Email $email)
    {
    }
}
