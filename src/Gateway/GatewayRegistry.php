<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

class GatewayRegistry
{
    /**
     * @var array<string,GatewayInterface>
     */
    private array $gatewaysByName = [];

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
}
