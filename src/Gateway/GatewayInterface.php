<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Terminal42\NotificationCenterBundle\Config\GatewayConfig;
use Terminal42\NotificationCenterBundle\Config\LanguageConfig;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Config\NotificationConfig;
use Terminal42\NotificationCenterBundle\Exception\CouldNotCreateParcelException;
use Terminal42\NotificationCenterBundle\Parcel\ParcelInterface;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

interface GatewayInterface
{
    public function getName(): string;

    /**
     * @return class-string<ParcelInterface>
     */
    public function getParcelClass(): string;

    public function sendParcel(ParcelInterface $parcel): void; // TODO: result?

    /**
     * @throws CouldNotCreateParcelException
     */
    public function createParcelFromConfigs(TokenCollection $tokenCollection, NotificationConfig $notificationConfig, MessageConfig $messageConfig, GatewayConfig $gatewayConfig, LanguageConfig $languageConfig = null): ParcelInterface;
}
