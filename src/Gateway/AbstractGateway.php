<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\String\SimpleTokenParser;
use Psr\Container\ContainerInterface;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotSealParcelException;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\BulkyItemsStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

abstract class AbstractGateway implements GatewayInterface
{
    public const SERVICE_NAME_NOTIFICATION_CENTER = 'notification_center';

    public const SERVICE_NAME_BULKY_ITEM_STORAGE = 'bulky_item_storage';

    public const SERVICE_NAME_SIMPLE_TOKEN_PARSER = 'simple_token_parser';

    public const SERVICE_NAME_INSERT_TAG_PARSER = 'insert_tag_parser';

    protected ContainerInterface|null $container = null;

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function sealParcel(Parcel $parcel): Parcel
    {
        if (!$parcel->hasStamps($this->getRequiredStampsForSealing())) {
            throw CouldNotSealParcelException::becauseOfInsufficientStamps($parcel->getStampClasses(), $this->getRequiredStampsForSealing());
        }

        return $this->doSealParcel($parcel);
    }

    public function sendParcel(Parcel $parcel): Receipt
    {
        if (!$parcel->hasStamps($this->getRequiredStampsForSending())) {
            return Receipt::createForUnsuccessfulDelivery(
                $parcel,
                CouldNotDeliverParcelException::becauseOfInsufficientStamps(
                    $parcel->getStampClasses(),
                    $this->getRequiredStampsForSending(),
                ),
            );
        }

        return $this->doSendParcel($parcel);
    }

    /**
     * @return array<class-string<StampInterface>>
     */
    protected function getRequiredStampsForSealing(): array
    {
        return [];
    }

    /**
     * @return array<class-string<StampInterface>>
     */
    protected function getRequiredStampsForSending(): array
    {
        return [];
    }

    abstract protected function doSealParcel(Parcel $parcel): Parcel;

    abstract protected function doSendParcel(Parcel $parcel): Receipt;

    /**
     * You can provide an optional TokenCollection if you want to force a certain token collection. Otherwise, they
     * will be taken from the TokenCollectionStamp.
     */
    protected function replaceTokens(Parcel $parcel, string $value, TokenCollection|null $tokenCollection = null): string
    {
        if (!$simpleTokenParser = $this->getSimpleTokenParser()) {
            return $value;
        }

        $tokenCollection = $tokenCollection ?? $parcel->getStamp(TokenCollectionStamp::class)?->tokenCollection;

        if (!$tokenCollection instanceof TokenCollection) {
            return $value;
        }

        return $simpleTokenParser->parse(
            $value,
            $tokenCollection->forSimpleTokenParser(),
        );
    }

    protected function replaceInsertTags(string $value): string
    {
        if ($insertTagParser = $this->getInsertTagParser()) {
            return $insertTagParser->replaceInline($value);
        }

        return $value;
    }

    /**
     * You can provide an optional TokenCollection if you want to force a certain token collection. Otherwise, they
     * will be taken from the TokenCollectionStamp.
     */
    protected function replaceTokensAndInsertTags(Parcel $parcel, string $value, TokenCollection|null $tokenCollection = null): string
    {
        return $this->replaceInsertTags($this->replaceTokens($parcel, $value, $tokenCollection));
    }

    protected function isBulkyItemVoucher(Parcel $parcel, string $voucher): bool
    {
        if (!$parcel->hasStamp(BulkyItemsStamp::class)) {
            return false;
        }

        /** @var BulkyItemsStamp $bulkyItemsStamp */
        $bulkyItemsStamp = $parcel->getStamp(BulkyItemsStamp::class);

        return $bulkyItemsStamp->has($voucher);
    }

    protected function getSimpleTokenParser(): SimpleTokenParser|null
    {
        if (null === $this->container || !$this->container->has(self::SERVICE_NAME_SIMPLE_TOKEN_PARSER)) {
            return null;
        }

        $simpleTokenParser = $this->container->get(self::SERVICE_NAME_SIMPLE_TOKEN_PARSER);

        return !$simpleTokenParser instanceof SimpleTokenParser ? null : $simpleTokenParser;
    }

    protected function getInsertTagParser(): InsertTagParser|null
    {
        if (null === $this->container || !$this->container->has(self::SERVICE_NAME_INSERT_TAG_PARSER)) {
            return null;
        }

        $insertTagParser = $this->container->get(self::SERVICE_NAME_INSERT_TAG_PARSER);

        return !$insertTagParser instanceof InsertTagParser ? null : $insertTagParser;
    }

    protected function getNotificationCenter(): NotificationCenter|null
    {
        if (null === $this->container || !$this->container->has(self::SERVICE_NAME_NOTIFICATION_CENTER)) {
            return null;
        }

        $notificationCenter = $this->container->get(self::SERVICE_NAME_NOTIFICATION_CENTER);

        return !$notificationCenter instanceof NotificationCenter ? null : $notificationCenter;
    }

    protected function getBulkyItemStorage(): BulkyItemStorage|null
    {
        if (null === $this->container || !$this->container->has(self::SERVICE_NAME_BULKY_ITEM_STORAGE)) {
            return null;
        }

        $bulkyItemStorage = $this->container->get(self::SERVICE_NAME_BULKY_ITEM_STORAGE);

        return !$bulkyItemStorage instanceof BulkyItemStorage ? null : $bulkyItemStorage;
    }
}
