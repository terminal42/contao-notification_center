<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Terminal42\NotificationCenterBundle\Parcel\Parcel;

interface GatewayInterface
{
    public function getName(): string;

    public function sendParcel(Parcel $parcel): void; // TODO: result?
}
