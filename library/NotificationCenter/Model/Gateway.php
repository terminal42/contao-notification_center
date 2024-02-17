<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Model;

use Contao\Model;
use Contao\System;
use NotificationCenter\Gateway\GatewayInterface;

class Gateway extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_nc_gateway';

    /**
     * Gateway instance
     * @var GatewayInterface
     */
    protected $objGateway;


    /**
     * Get gateway instance
     * @return  GatewayInterface|null
     */
    public function getGateway()
    {
        // We only need to build the gateway once, Model is cached by registry and Gateway does not change between messages
        if (null === $this->objGateway) {
            $strClass = $GLOBALS['NOTIFICATION_CENTER']['GATEWAY'][$this->type];
            if (!class_exists($strClass)) {
                System::log(sprintf('Could not find gateway class "%s".', $strClass), __METHOD__, TL_ERROR);

                return null;
            }

            try {
                $objGateway = new $strClass($this);

                if (!$objGateway instanceof GatewayInterface) {
                    System::log(sprintf('The gateway class "%s" must be an instance of GatewayInterface.', $strClass), __METHOD__, TL_ERROR);

                    return null;
                }

                $this->objGateway = $objGateway;

            } catch (\Exception $e) {
                System::log(sprintf('There was a general error building the gateway: "%s".', $e->getMessage()), __METHOD__, TL_ERROR);

                return null;
            }
        }

        return $this->objGateway;
    }

    /**
     * Find queues by interval.
     *
     * @param   string $interval
     * @param   array  $options
     *
     * @return Gateway[]|null
     */
    public static function findQueuesByInterval($interval, $options = array())
    {
        $t = static::$strTable;

        $columns = array("$t.type=?", "$t.queue_cronEnable=?", "$t.queue_cronInterval=?");
        $values  = array('queue', 1, $interval);

        return static::findBy($columns, $values, $options);
    }
}
