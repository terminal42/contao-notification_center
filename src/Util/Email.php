<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Util;

use Contao\StringUtil;
use Contao\Validator;

class Email
{
    public static function splitEmailAddresses(string $recipients): array
    {
        $split = [];

        foreach (StringUtil::trimsplit(',', $recipients) as $address) {
            if ('' === $address) {
                continue;
            }

            [, $email] = StringUtil::splitFriendlyEmail($address);

            if ('' === $email || !Validator::isEmail($email)) {
                continue;
            }

            $split[] = $address;
        }

        return $split;
    }
}
