<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeRegistry;

class NotificationListener
{
    public function __construct(private readonly NotificationTypeRegistry $typeRegistry)
    {
    }

    /**
     * Adjust the operations to adjust the UX for 4.13 (pencil accesses messages, header.svg edits notification) and
     * 5.x (pencil edits notification, children.svg accesses messages).
     */
    #[AsCallback(table: 'tl_nc_notification', target: 'config.onload')]
    public function onLoadCallback(): void
    {
        if (version_compare(ContaoCoreBundle::getVersion(), '5.0', '<')) {
            $GLOBALS['TL_DCA']['tl_nc_notification']['list']['operations']['edit'] = [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_notification']['children'],
                'href' => 'table=tl_nc_message',
                'icon' => 'edit.svg',
            ];
            $GLOBALS['TL_DCA']['tl_nc_notification']['list']['operations']['children'] = [
                'label' => &$GLOBALS['TL_LANG']['tl_nc_notification']['edit'],
                'href' => 'act=edit',
                'icon' => 'header.svg',
            ];
        }
    }

    /**
     * @return array<string>
     */
    #[AsCallback(table: 'tl_nc_notification', target: 'fields.type.options')]
    public function onTypeOptionsCallback(): array
    {
        return array_keys($this->typeRegistry->all());
    }
}
