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

    /**
     * @template T of object
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

    private function setParameter(string $key, mixed $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function serialize(): string
    {
        return json_encode($this->parameters);
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
}
