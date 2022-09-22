<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

class MessageListener
{
    use OverrideDefaultPaletteTrait;

    public function __construct(private Connection $connection, private ContaoFramework $framework)
    {
    }

    #[AsCallback(table: 'tl_nc_message', target: 'config.onload')]
    public function onLoadCallback(DataContainer $dc): void
    {
        $currentRecord = $this->getCurrentRecord($dc);
        $this->overrideDefaultPaletteForGateway((int) $currentRecord['gateway'], 'tl_nc_message');
    }

    #[AsCallback(table: 'tl_nc_message', target: 'fields.email_template.options')]
    public function onTokenTransformerOptionsCallback(): array
    {
        return $this->framework->getAdapter(Controller::class)->getTemplateGroup('mail_');
    }
}
