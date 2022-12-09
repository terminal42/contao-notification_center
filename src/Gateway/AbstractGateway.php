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
        if (null === $this->container || !$this->container->has('simple_token_parser')) {
            return null;
        }

        $simpleTokenParser = $this->container->get('simple_token_parser');

        return !$simpleTokenParser instanceof SimpleTokenParser ? null : $simpleTokenParser;
    }

    protected function getInsertTagParser(): InsertTagParser|null
    {
        if (null === $this->container || !$this->container->has('insert_tag_parser')) {
            return null;
        }

        $insertTagParser = $this->container->get('insert_tag_parser');

        return !$insertTagParser instanceof InsertTagParser ? null : $insertTagParser;
    }
}
