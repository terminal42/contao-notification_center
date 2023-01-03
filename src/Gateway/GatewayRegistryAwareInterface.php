<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

interface GatewayRegistryAwareInterface
{
    public function setGatewayRegistry(GatewayRegistry $gatewayRegistry): void;
}
