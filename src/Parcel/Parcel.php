<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\UnserializableStampInterface;

final class Parcel
{
    /**
     * @var array<class-string,StampInterface>
     */
    private array $stamps = [];

    /**
     * @param array<StampInterface> $stamps
     */
    public function __construct(private MessageConfig $messageConfig, array $stamps = [])
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

    public function withStamp(StampInterface $stamp): self
    {
        $clone = clone $this;

        $clone->addStamp($stamp);

        return $clone;
    }

    public function withJustOneStamp(StampInterface $stamp): self
    {
        $clone = $this->withoutStamps();

        $clone->addStamp($stamp);

        return $clone;
    }

    public function withoutStamp(StampInterface $stamp): self
    {
        $clone = clone $this;

        unset($clone->stamps[$stamp::class]);

        return $clone;
    }

    public function withoutStamps(): self
    {
        $clone = clone $this;

        $clone->stamps = [];

        return $clone;
    }

    public function serialize(): string
    {
        $data = [
            'messageConfig' => $this->messageConfig->serialize(),
            'stamps' => [],
        ];

        foreach ($this->stamps as $stamp) {
            $data['stamps'][$stamp::class] = $stamp->serialize();
        }

        return json_encode($data);
    }

    public static function fromSerialized(string $serialized): self
    {
        $data = json_decode($serialized, true);

        $parcel = new self(MessageConfig::fromSerialized($data['messageConfig']));

        foreach ($data['stamps'] as $class => $stampData) {
            if (is_a($class, UnserializableStampInterface::class, true)) {
                $parcel->addStamp($class::fromSerialized($stampData));
            }
        }

        return $parcel;
    }

    private function addStamp(StampInterface $stamp): self
    {
        $this->stamps[$stamp::class] = $stamp;

        return $this;
    }
}
