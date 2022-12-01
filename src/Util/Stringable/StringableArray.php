<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Util\Stringable;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<mixed>
 */
class StringableArray extends AbstractCollection implements \Stringable
{
    public function __toString(): string
    {
        $chunks = [];

        foreach ($this->data as $k => $v) {
            if (!\is_string($v)) {
                $chunks[] = $k.' ['.json_encode($v).']';
            } else {
                $chunks[] = $v;
            }
        }

        return implode(', ', $chunks);
    }

    public function getType(): string
    {
        return 'mixed';
    }
}
