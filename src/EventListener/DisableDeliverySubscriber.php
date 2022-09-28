<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Contao\CoreBundle\String\SimpleTokenParser;
use Contao\PageModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsEvent;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailToken;
use Terminal42\NotificationCenterBundle\Token\Token;

class DisableDeliverySubscriber implements EventSubscriberInterface
{
    public function __construct(private SimpleTokenParser $simpleTokenParser, private ExpressionLanguage $expressionLanguage) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CreateParcelEvent::class => 'onCreateParcel',
        ];
    }

    public function onCreateParcel(CreateParcelEvent $event): void
    {
        $messageConfig = $event->getParcel()->getMessageConfig();

        if (!$messageConfig->isPublished()) {
            $event->disableDelivery();
            return;
        }

        $now = new \DateTimeImmutable();

        if (null !== ($start = $messageConfig->getStart()) && $now < $start) {
            $event->disableDelivery();
            return;
        }

        if (null !== ($stop = $messageConfig->getStop()) && $now >= $stop) {
            $event->disableDelivery();
            return;
        }

        if ('' !== $messageConfig->getCondition() &&
            null !== ($tokenCollectionStamp = $event->getParcel()->getStamp(TokenCollectionStamp::class))
        ) {
            // We first replace tokens on the condition. So that e.g. "##form_email"## === 'foobar@foobar.com'" becomes
            // "form_email === 'foobar@foobar.com'". For this, we can only work with string token values.
            $tokens = $tokenCollectionStamp->tokenCollection->asRawKeyValueWithStringsOnly();
            $tokensForCondition = array_combine(array_keys($tokens), array_keys($tokens));
            $condition = $this->simpleTokenParser->parse($messageConfig->getCondition(), $tokensForCondition);

            // Now we have a ready to be evaluated expression. The expression language supports objects though so we
            // can also pass those tokens to it.
            $tokens = $tokenCollectionStamp->tokenCollection->asRawKeyValue();

            if (!$this->expressionLanguage->evaluate($condition, $tokens)) {
                $event->disableDelivery();
            }
        }
    }
}
