<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

interface GatewayInterface
{
    public function getName(): string;

    public function sendParcel(Parcel $parcel): void; // TODO: result?
}
