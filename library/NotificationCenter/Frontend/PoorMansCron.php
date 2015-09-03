<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */


namespace NotificationCenter\Frontend;


use NotificationCenter\Model\Gateway;

class PoorMansCron
{
    public function minutely() { $this->sendMessagesFromQueue('minutely'); }
    public function hourly()   { $this->sendMessagesFromQueue('hourly');   }
    public function daily()    { $this->sendMessagesFromQueue('daily');    }
    public function weekly()   { $this->sendMessagesFromQueue('weekly');   }
    public function monthly()  { $this->sendMessagesFromQueue('monthly');  }

    /**
     * Triggers queues and sends their messages based on poor man cron jobs.
     *
     * @param string $interval
     */
    private function sendMessagesFromQueue($interval)
    {
        $queueGateways = Gateway::findQueuesByInterval($interval);

        if ($queueGateways === null) {

            return;
        }

        /** @var $queueManager \NotificationCenter\Queue\QueueManagerInterface */
        $queueManager = $GLOBALS['NOTIFICATION_CENTER']['QUEUE_MANAGER'];

        foreach ($queueGateways as $queueGateway) {
            $queueManager->sendFromQueue($queueGateway->id, (int) $queueGateway->queue_cronMessages);
        }
    }
}
