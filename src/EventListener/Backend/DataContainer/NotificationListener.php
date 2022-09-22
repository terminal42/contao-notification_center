<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Terminal42\NotificationCenterBundle\MessageType\MessageTypeRegistry;

class NotificationListener
{
    public function __construct(private MessageTypeRegistry $typeRegistry, private ContaoFramework $framework)
    {
    }

    #[AsCallback(table: 'tl_nc_notification', target: 'fields.type.options')]
    public function onTypeOptionsCallback(): array
    {
        return array_keys($this->typeRegistry->all());
    }

    #[AsCallback(table: 'tl_nc_notification', target: 'fields.token_transformer.options')]
    public function onTokenTransformerOptionsCallback(): array
    {
        $templates = $this->framework->getAdapter(Controller::class)->getTemplateGroup('nc_token_transformer_');
        // TODO: fix me
        return [];
    }
}
