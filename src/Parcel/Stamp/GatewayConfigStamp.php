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

    public static function fromArray(array $data): self
    {
        return new self(GatewayConfig::fromArray($data));
    }
}
