<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Terminal42\NotificationCenterBundle\Parcel\ParcelInterface;

class GatewayRegistry
{
    /**
     * @var array<string,GatewayInterface>
     */
    private array $gatewaysByName = [];

    /**
     * @var array<string,GatewayInterface>
     */
    private array $gatewaysByParcelClass = [];

    /**
     * @param iterable<GatewayInterface> $gateways
     */
    public function __construct(iterable $gateways)
    {
        foreach ($gateways as $gateway) {
            $this->add($gateway);
        }
    }

    public function add(GatewayInterface $gateway): self
    {
        $this->gatewaysByName[$gateway->getName()] = $gateway;
        $this->gatewaysByParcelClass[$gateway->getParcelClass()] = $gateway;

        return $this;
    }

    /**
     * @return array<string,GatewayInterface>
     */
    public function all(): array
    {
        return $this->gatewaysByName;
    }

    public function getByName(string $name): GatewayInterface|null
    {
        return $this->gatewaysByName[$name] ?? null;
    }

    public function getByParcel(ParcelInterface|string $parcel): GatewayInterface|null
    {
        $class = $parcel instanceof ParcelInterface ? $parcel::class : $parcel;

        return $this->gatewaysByParcelClass[$class] ?? null;
    }
}
