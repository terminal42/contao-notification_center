<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

abstract class AbstractConfig
{
    private array $parameters = [];

    private function __construct()
    {
    }

    public function withParameter(string $key, mixed $value): static
    {
        $clone = clone $this;
        $clone->setParameter($key, $value);

        return $clone;
    }

    private function setParameter(string $key, mixed $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function all(): array
    {
        return $this->parameters;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return \array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function getObject(string $key, string $className): object|null
    {
        $value = $this->get($key);

        if (null === $value || !is_a($value, $className, true)) {
            return null;
        }

        return $value;
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

    public static function fromArray(array $parameters): static
    {
        $object = new static();

        foreach ($parameters as $k => $p) {
            $object->setParameter($k, $p);
        }

        return $object;
    }
}
