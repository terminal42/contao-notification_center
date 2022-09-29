<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\OptIn;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\OptIn\OptInInterface;
use Contao\CoreBundle\OptIn\OptInTokenInterface;

/**
 * Decorates the core opt in service in order to have a token which sends via the
 * notification center rather than the core Email class.
 */
class OptIn implements OptInInterface
{
    public function __construct(private OptInInterface $decorated, private ContaoFramework $framework)
    {
    }

    public function create(string $prefix, string $email, array $related): OptInTokenInterface
    {
        dd(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3));

        return $this->decorated->create($prefix, $email, $related);

        if ('pw' !== $prefix) {
            return $this->decorated->create($prefix, $email, $related);
        }

        // TODO: Implement once we know, where the token is coming from

        return $this->wrapLostPasswordToken($this->decorated->create($prefix, $email, $related));
    }

    public function find(string $identifier): OptInTokenInterface|null
    {
        return $this->decorated->find($identifier);

        // TODO: Implement once we know, where the token is coming from

        return $this->wrapLostPasswordToken($this->decorated->find($identifier));
    }

    public function purgeTokens(): void
    {
        $this->decorated->purgeTokens();
    }

    private function wrapLostPasswordToken(OptInTokenInterface $inner): LostPasswordOptInToken
    {
        dd($inner, $inner->getRelatedRecords());

        return new LostPasswordOptInToken($inner, $this->framework);
    }
}
