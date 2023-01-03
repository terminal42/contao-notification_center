<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

use Terminal42\NotificationCenterBundle\Config\LanguageConfig;

class LanguageConfigStamp extends AbstractConfigStamp
{
    public function __construct(public LanguageConfig $languageConfig)
    {
        parent::__construct($this->languageConfig);
    }

    public static function fromArray(array $data): self
    {
        return new self(LanguageConfig::fromArray($data));
    }
}
