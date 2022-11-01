<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Terminal42\NotificationCenterBundle\MessageType\MessageTypeRegistry;

class NotificationListener
{
    public function __construct(private MessageTypeRegistry $typeRegistry)
    {
    }

    /**
     * Adjust the operations to adjust the UX for 4.13 (pencil accesses messages, header.svg edits notification) and
     * 5.x (pencil edits notification, children.svg accesses messages).
     */
    #[AsCallback(table: 'tl_nc_notification', target: 'config.onload')]
    public function onLoadCallback(): void
    {
        $operationsToUnset = version_compare(ContaoCoreBundle::getVersion(), '5.0', '>=')
            ? ['edit-413', 'children-413']
            : ['edit-5', 'children-5']
        ;

        foreach ($operationsToUnset as $key) {
            unset($GLOBALS['TL_DCA']['tl_nc_notification']['list']['operations'][$key]);
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
