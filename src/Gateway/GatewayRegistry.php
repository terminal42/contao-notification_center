<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

class GatewayRegistry
{
    /**
     * @var array<GatewayInterface>
     */
    private array $gateways = [];

    public function __construct(iterable $gateways)
    {
        foreach ($gateways as $gateway) {
            $this->add($gateway);
        }
    }

    public function add(GatewayInterface $gateway): self
    {
        $this->gateways[$gateway->getName()] = $gateway;

        return $this;
    }

    /**
     * @return array<string,GatewayInterface>
     */
    public function all(): array
    {
        return $this->gateways;
    }

    public function getByName(string $name): GatewayInterface|null
    {
        return $this->gateways[$name] ?? null;
    }
}
