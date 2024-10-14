<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Parcel\Stamp;

use Terminal42\NotificationCenterBundle\Config\FormConfig;

class FormConfigStamp extends AbstractConfigStamp
{
    public function __construct(public FormConfig $formConfig)
    {
        parent::__construct($this->formConfig);
    }

    public static function fromArray(array $data): self
    {
        return new self(FormConfig::fromArray($data));
    }
}
