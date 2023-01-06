<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel;

use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;
use Terminal42\NotificationCenterBundle\Util\Json;

final class Parcel
{
    private StampCollection $stampsBeforeSealing;
    private StampCollection $stampsAfterSealing;
    private bool $sealed = false;

    public function __construct(private MessageConfig $messageConfig)
    {
        $this->stampsBeforeSealing = new StampCollection();
        $this->stampsAfterSealing = new StampCollection();
    }

    public function seal(): self
    {
        if ($this->isSealed()) {
            return $this;
        }

        $clone = clone $this;
        $clone->sealed = true;

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
        return $this->stampsBeforeSealing->has($class) || $this->stampsAfterSealing->has($class);
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

    public function getStampClasses(): array
    {
        return $this->stampsBeforeSealing->getClasses();
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
        // Stamps after sealing have priority
        if (null !== ($stamp = $this->stampsAfterSealing->get($className))) {
            return $stamp;
        }

        return $this->stampsBeforeSealing->get($className);
    }

    public function withStamp(StampInterface $stamp): self
    {
        $clone = clone $this;

        if ($this->isSealed()) {
            $clone->stampsAfterSealing = $this->stampsAfterSealing
                ->with($stamp);
        } else {
            $clone->stampsBeforeSealing = $this->stampsBeforeSealing
                ->with($stamp);
        }

        return $clone;
    }

    public function serialize(): string
    {
        return Json::utf8SafeEncode($this->toArray());
    }

    public static function fromSerialized(string $serialized): self
    {
        return self::fromArray(Json::utf8SafeDecode($serialized));
    }

    public function toArray(): array
    {
        return [
            'messageConfig' => $this->messageConfig->toArray(),
            'stampsBeforeSealing' => $this->stampsBeforeSealing->toArray(),
            'stampsAfterSealing' => $this->stampsAfterSealing->toArray(),
            'sealed' => $this->sealed,
        ];
    }

    /**
     * Will return an instance of the parcel as it was before it was sealed.
     * This means, the stamps that were added AFTER a parcel was sealed will not
     * be present on the new instance.
     */
    public function unseal(): self
    {
        if (!$this->isSealed()) {
            return $this;
        }

        $parcel = new self($this->getMessageConfig());

        foreach ($this->stampsBeforeSealing->all() as $stamp) {
            $parcel = $parcel->withStamp($stamp);
        }

        return $parcel;
    }

    public static function fromArray(array $data): self
    {
        $parcel = new self(
            MessageConfig::fromArray($data['messageConfig']),
        );

        $parcel->stampsBeforeSealing = StampCollection::fromArray($data['stampsBeforeSealing']);
        $parcel->stampsAfterSealing = StampCollection::fromArray($data['stampsAfterSealing']);
        $parcel->sealed = $data['sealed'];

        return $parcel;
    }
}
