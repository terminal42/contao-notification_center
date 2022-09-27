<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;

class MessageListener
{
    public function __construct(private ConfigLoader $configLoader, private ContaoFramework $framework)
    {
    }

    #[AsCallback(table: 'tl_nc_message', target: 'config.onload')]
    public function onLoadCallback(DataContainer $dc): void
    {
        if (
            null === ($message = $this->configLoader->loadMessage($dc->id))
            || null === ($gateway = $this->configLoader->loadGateway($message->getGateway()))
        ) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_nc_language']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_nc_language']['palettes'][$gateway->getType()];
    }

    /**
     * @return array<string>
     */
    #[AsCallback(table: 'tl_nc_message', target: 'fields.email_template.options')]
    public function onTokenTransformerOptionsCallback(): array
    {
        return $this->framework->getAdapter(Controller::class)->getTemplateGroup('mail_');
    }
}
