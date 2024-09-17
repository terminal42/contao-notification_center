<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Util;

class Json
{
    /**
     * @param array<mixed> $data
     */
    public static function utf8SafeEncode(array $data, int $flags = 0): string|false
    {
        return json_encode(self::recursiveBase64Encode($data), $flags);
    }

    /**
     * @return array<mixed>|false
     */
    public static function utf8SafeDecode(string $encoded): array|false
    {
        $data = json_decode($encoded, true);

        if (false === $data) {
            return false;
        }

        return self::recursiveBase64Decode($data);
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private static function recursiveBase64Encode(array $data): array
    {
        foreach ($data as $k => $v) {
            if (\is_array($v)) {
                $data[$k] = self::recursiveBase64Encode($v);
            } else {
                if (\is_string($v) && 1 !== preg_match('//u', $v)) {
                    $data[$k] = 'base64://'.base64_encode($v);
                } else {
                    $data[$k] = $v;
                }
            }
        }

        return $data;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private static function recursiveBase64Decode(array $data): array
    {
        foreach ($data as $k => $v) {
            if (\is_array($v)) {
                $data[$k] = self::recursiveBase64Decode($v);
            } else {
                if (\is_string($v) && str_starts_with($v, 'base64://')) {
                    $data[$k] = base64_decode(substr($v, 9), true);
                } else {
                    $data[$k] = $v;
                }
            }
        }

        return $data;
    }
}
