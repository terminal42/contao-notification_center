<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Config\GatewayConfig;
use Terminal42\NotificationCenterBundle\Config\LanguageConfig;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Config\NotificationConfig;
use Terminal42\NotificationCenterBundle\Parcel\ParcelInterface;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class CreateParcelEvent extends Event
{
    public function __construct(
        private ParcelInterface $parcel,
        private TokenCollection $tokenCollection,
        private NotificationConfig $notificationConfig,
        private MessageConfig $messageConfig,
        private GatewayConfig $gatewayConfig,
        private LanguageConfig|null $languageConfig = null,
    ) {
    }

    public function getTokenCollection(): TokenCollection
    {
        return $this->tokenCollection;
    }

    public function getNotificationConfig(): NotificationConfig
    {
        return $this->notificationConfig;
    }

    public function getMessageConfig(): MessageConfig
    {
        return $this->messageConfig;
    }

    public function getGatewayConfig(): GatewayConfig
    {
        return $this->gatewayConfig;
    }

    public function getLanguageConfig(): LanguageConfig|null
    {
        return $this->languageConfig;
    }

    public function getParcel(): ParcelInterface
    {
        return $this->parcel;
    }

    public function setParcel(ParcelInterface $parcel): self
    {
        $this->parcel = $parcel;

        return $this;
    }
}
