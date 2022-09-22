<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Terminal42\NotificationCenterBundle\Config\GatewayConfig;
use Terminal42\NotificationCenterBundle\Config\LanguageConfig;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Config\NotificationConfig;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class Parcel
{
    public function __construct(
        public NotificationConfig $notificationConfig,
        public MessageConfig $messageConfig,
        public GatewayConfig $gatewayConfig,
        public TokenCollection $tokenCollection,
        public LanguageConfig|null $languageConfig = null,
    ) {
    }

    public function withLanguageConfig(LanguageConfig $languageConfig): self
    {
        $clone = clone $this;
        $clone->languageConfig = $languageConfig;

        return $clone;
    }
}
