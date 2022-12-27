<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;

final class Parcel
{
    private bool $sealed = false;

    public function __construct(private MessageConfig $messageConfig, private StampCollection $stamps)
    {
    }

    public function seal(): self
    {
        if ($this->isSealed()) {
            return $this;
        }

        $clone = clone $this;
        $clone->sealed = true;
        $clone->stamps = $this->stamps->seal();

        return $clone;
    }

    public function isSealed(): bool
    {
        return $this->sealed;
    }

    public function getMessageConfig(): MessageConfig
    {
        return $this->messageConfig;
    }

    public function hasStamp(string $class): bool
    {
        return $this->stamps->has($class);
    }

    /**
     * @param array<class-string<StampInterface>> $classes
     */
    public function hasStamps(array $classes): bool
    {
        return $this->stamps->hasMultiple($classes);
    }

    public function getStamps(): StampCollection
    {
        return $this->stamps;
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
        return $this->stamps->get($className);
    }

    public function withStamp(StampInterface $stamp): self
    {
        $clone = clone $this;
        $clone->stamps = $this->stamps
            ->with($stamp)
        ;

        return $clone;
    }

    public function serialize(): string
    {
        $data = [
            'messageConfig' => $this->messageConfig->serialize(),
            'stamps' => $this->stamps->serialize(),
            'sealed' => $this->sealed,
        ];

        return json_encode($data);
    }

    public static function fromSerialized(string $serialized): self
    {
        $data = json_decode($serialized, true);

        $parcel = new self(
            MessageConfig::fromSerialized($data['messageConfig']),
            StampCollection::fromSerialized($data['stamps']),
        );

        $parcel->sealed = $data['sealed'];

        return $parcel;
    }
}
