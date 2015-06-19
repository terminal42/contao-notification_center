<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2014
 * @license    LGPL
 */


namespace NotificationCenter\Frontend;


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
        /** @var $objQueueManager \NotificationCenter\Queue\QueueManagerInterface */
        $objQueueManager = $GLOBALS['NOTIFICATION_CENTER']['QUEUE_MANAGER'];

        //$objQueueManager->sendFromQueue();
    }
}
