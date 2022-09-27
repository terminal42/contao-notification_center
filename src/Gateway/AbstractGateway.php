<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Contao\CoreBundle\String\SimpleTokenParser;
use Psr\Container\ContainerInterface;
use Terminal42\NotificationCenterBundle\Exception\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;

abstract class AbstractGateway implements GatewayInterface
{
    public function __construct(protected ContainerInterface $serviceLocator)
    {
    }

    public function sendParcel(Parcel $parcel): void
    {
        if (!$parcel->hasStamps($this->getRequiredStamps())) {
            throw CouldNotDeliverParcelException::becauseOfInsufficientStamps($parcel->getStampClasses(), $this->getRequiredStamps());
        }

        $this->doSendParcel($parcel); // TODO: return
    }

    /**
     * @return array<class-string<StampInterface>>
     */
    abstract protected function getRequiredStamps(): array;

    abstract protected function doSendParcel(Parcel $parcel): void;

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
