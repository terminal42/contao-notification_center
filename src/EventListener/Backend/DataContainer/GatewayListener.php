<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Mailer\AvailableTransports;
use Terminal42\NotificationCenterBundle\Gateway\GatewayRegistry;

class GatewayListener
{
    public function __construct(private GatewayRegistry $gatewayRegistry, private AvailableTransports $availableTransports)
    {
    }

    #[AsCallback(table: 'tl_nc_gateway', target: 'fields.type.options')]
    public function onTypeOptionsCallback(): array
    {
        return array_keys($this->gatewayRegistry->all());
    }

    #[AsCallback(table: 'tl_nc_gateway', target: 'fields.mailerTransport.options')]
    public function onMailerOptionsCallback(): array
    {
        return $this->availableTransports->getTransportOptions();
    }
}
