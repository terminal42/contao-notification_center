<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\String\SimpleTokenParser;
use Psr\Container\ContainerInterface;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotFinalizeParcelException;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;

abstract class AbstractGateway implements GatewayInterface
{
    public const SERVICE_NAME_SIMPLE_TOKEN_PARSER = 'simple_token_parser';
    public const SERVICE_NAME_INSERT_TAG_PARSER = 'insert_tag_parser';

    protected ContainerInterface|null $container;

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function finalizeParcel(Parcel $parcel): Parcel
    {
        if (!$parcel->hasStamps($this->getRequiredStampsForFinalization())) {
            throw CouldNotFinalizeParcelException::becauseOfInsufficientStamps($parcel->getStampClasses(), $this->getRequiredStampsForFinalization());
        }

        return $this->doFinalizeParcel($parcel);
    }

    public function sendParcel(Parcel $parcel): Receipt
    {
        if (!$parcel->hasStamps($this->getRequiredStampsForSending())) {
            return Receipt::createForUnsuccessfulDelivery(
                $parcel,
                CouldNotDeliverParcelException::becauseOfInsufficientStamps(
                    $parcel->getStampClasses(),
                    $this->getRequiredStampsForSending()
                )
            );
        }

        return $this->doSendParcel($parcel);
    }

    /**
     * @return array<class-string<StampInterface>>
     */
    protected function getRequiredStampsForFinalization(): array
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

    abstract protected function doFinalizeParcel(Parcel $parcel): Parcel;

    abstract protected function doSendParcel(Parcel $parcel): Receipt;

    protected function replaceTokens(Parcel $parcel, string $value): string
    {
        if (!$parcel->hasStamp(TokenCollectionStamp::class)) {
            return $value;
        }

        $simpleTokenParser = $this->getSimpleTokenParser();

        if (null === $simpleTokenParser) {
            return $value;
        }

        return $simpleTokenParser->parse(
            $value,
            $parcel->getStamp(TokenCollectionStamp::class)->tokenCollection->asKeyValue()
        );
    }

    protected function replaceInsertTags(string $value): string
    {
        $insertTagParser = $this->getInsertTagParser();

        if (null === $insertTagParser) {
            return $value;
        }

        return $insertTagParser->replaceInline($value);
    }

    protected function replaceTokensAndInsertTags(Parcel $parcel, string $value): string
    {
        return $this->replaceInsertTags($this->replaceTokens($parcel, $value));
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
}
