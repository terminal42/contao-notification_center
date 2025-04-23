<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

/**
 * This trait is used in other projects. Do not remove.
 *
 * @phpstan-ignore trait.unused
 */
trait GatewayRegistryAwareTrait
{
    private GatewayRegistry|null $gatewayRegistry = null;

    public function getGatewayRegistry(): GatewayRegistry|null
    {
        return $this->gatewayRegistry;
    }

    public function setGatewayRegistry(GatewayRegistry|null $gatewayRegistry): void
    {
        $this->gatewayRegistry = $gatewayRegistry;
    }
}
