<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Util;

class ParameterBag
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters = [];

    final private function __construct()
    {
    }

    public function withParameter(string $key, mixed $value): static
    {
        $clone = clone $this;
        $clone->setParameter($key, $value);

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->parameters;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return \array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    public function getString(string $key, string $default = ''): string
    {
        return (string) $this->get($key, $default);
    }

    public function getInt(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }

    public function getBoolean(string $key, bool $default = false): bool
    {
        return (bool) $this->get($key, $default);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        // Ensure encoding works even if non utf8 parameters are used.
        return $this->parameters;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public static function fromArray(array $parameters): static
    {
        $instance = new static();

        foreach ($parameters as $k => $p) {
            $instance->setParameter($k, $p);
        }

        return $instance;
    }

    private function setParameter(string $key, mixed $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }
}
