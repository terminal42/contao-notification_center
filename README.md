Notification Center
===================

The purpose of this extension is to provide a central and flexible way for
Contao developers to send notifications via their extensions.

If we can get this extension to be widely used, users will quickly get used
to the way one can configure the notification center.

## Adding your own bag type

```
// config.php
$GLOBALS['NOTIFICATION_CENTER']['BAGTYPE']['iso_order_confirmation'] = 'Isotope\NotificationCenter\BagType\OrderConfirmation';

// OrderConfirmation.php
namespace Isotope\NotificationCenter\BagType;

use NotificationCenter\BagType\BagTypeInterface;
use NotificationCenter\BagType\Base;


class OrderConfirmation extends Base implements BagTypeInterface
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
    public function getFileTokens()
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


## Sending a notification bag

```
NotificationCenter\Bag::send($intNotificationBagId, $arrTokens);