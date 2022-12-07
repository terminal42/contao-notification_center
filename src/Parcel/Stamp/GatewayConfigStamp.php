<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

use Terminal42\NotificationCenterBundle\Config\GatewayConfig;

class GatewayConfigStamp extends AbstractConfigStamp
{
    public function __construct(public GatewayConfig $gatewayConfig)
    {
        parent::__construct($this->gatewayConfig);
    }

    public static function fromSerialized(string $serialized): self
    {
        return new self(GatewayConfig::fromArray(json_decode($serialized, true)));
    }
}
