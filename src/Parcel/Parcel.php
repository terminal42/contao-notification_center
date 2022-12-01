<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

final class Parcel
{
    /**
     * @var array<class-string,StampInterface>
     */
    private array $stamps = [];

    /**
     * @param array<StampInterface> $stamps
     */
    public function __construct(private MessageConfig $messageConfig, array $stamps)
    {
        foreach ($stamps as $stamp) {
            $this->addStamp($stamp);
        }
    }

    public function getMessageConfig(): MessageConfig
    {
        return $this->messageConfig;
    }

    public function hasStamp(string $class): bool
    {
        return \array_key_exists($class, $this->stamps);
    }

    /**
     * @param array<class-string<StampInterface>> $classes
     */
    public function hasStamps(array $classes): bool
    {
        foreach ($classes as $class) {
            if (!$this->hasStamp($class)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<class-string<StampInterface>,StampInterface>
     */
    public function getStamps(): array
    {
        return $this->stamps;
    }

    /**
     * @return array<class-string<StampInterface>>
     */
    public function getStampClasses(): array
    {
        return array_keys($this->stamps);
    }

    /**
     * @template T of StampInterface
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function getStamp(string $className): StampInterface|null
    {
        return $this->stamps[$className] ?? null;
    }

    public function withStamp(StampInterface $stamp): static
    {
        $clone = clone $this;

        $clone->addStamp($stamp);

        return $clone;
    }

    public function withoutStamp(StampInterface $stamp): static
    {
        $clone = clone $this;

        unset($clone->stamps[$stamp::class]);

        return $clone;
    }

    public function serialize(): string
    {
        return json_encode($this->forSerialization());
    }

    private function addStamp(StampInterface $stamp): self
    {
        $this->stamps[$stamp::class] = $stamp;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    private function forSerialization(): array
    {
        $data = [];

        foreach ($this->stamps as $stamp) {
            $data[$stamp::class] = $stamp->serialize();
        }

        return $data;
    }
}
