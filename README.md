Notification Center
===================

The purpose of this extension is to provide a central and flexible way for
Contao developers to send notifications via their extensions.

If we can get this extension to be widely used, users will quickly get used
to the way one can configure the notification center.

## Adding your own notification type

```
// config.php
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATIONTYPE']['iso_order_confirmation'] = 'Isotope\NotificationCenter\NotificationType\OrderConfirmation';

// OrderConfirmation.php
namespace Isotope\NotificationCenter\NotificationType;

use NotificationCenter\NotificationType\NotificationTypeInterface;
use NotificationCenter\NotificationType\Base;


class OrderConfirmation extends Base implements NotificationTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRecipientTokens()
    {
        return array('recipient_email');
    }

    /**
     * {@inheritdoc}
     */
    public function getTextTokens()
    {
        return array('cart_text');
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachmentTokens()
    {
        return array('file');
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenDescription($strToken)
    {
        return 'Description for $strToken';
    }
}
```


## Sending a notification

```
NotificationCenter\Notification::send($intNotificationId, $arrTokens);