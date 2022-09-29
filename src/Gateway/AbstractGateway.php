<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\CoreBundle\String\SimpleTokenParser;
use Psr\Container\ContainerInterface;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;

abstract class AbstractGateway implements GatewayInterface
{
    public function __construct(protected ContainerInterface $serviceLocator)
    {
    }

    public function sendParcel(Parcel $parcel): Receipt
    {
        if (!$parcel->hasStamps($this->getRequiredStamps())) {
            return Receipt::createForUnsuccessfulDelivery(
                $parcel,
                CouldNotDeliverParcelException::becauseOfInsufficientStamps(
                    $parcel->getStampClasses(),
                    $this->getRequiredStamps()
                )
            );
        }

        return $this->doSendParcel($parcel);
    }

    /**
     * @return array<class-string<StampInterface>>
     */
    abstract protected function getRequiredStamps(): array;

    abstract protected function doSendParcel(Parcel $parcel): Receipt;

    protected function replaceTokens(Parcel $parcel, string $value): string
    {
        if (!$parcel->hasStamp(TokenCollectionStamp::class)) {
            return $value;
        }

        if (!$this->serviceLocator->has('contao.string.simple_token_parser')) {
            return $value;
        }

        /** @var SimpleTokenParser $simpleTokenParser */
        $simpleTokenParser = $this->serviceLocator->get('contao.string.simple_token_parser');

        return $simpleTokenParser->parse(
            $value,
            $parcel->getStamp(TokenCollectionStamp::class)->tokenCollection->asRawKeyValueWithStringsOnly()
        );
    }
}
