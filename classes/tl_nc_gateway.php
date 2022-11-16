<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter;

use Haste\Dca\PaletteManipulator;
use NotificationCenter\Gateway\LabelCallbackInterface;
use NotificationCenter\Model\Gateway;

class tl_nc_gateway extends \Backend
{
    /**
     * Loads the language file tl_settings
     */
    public function loadSettingsLanguageFile()
    {
        \System::loadLanguageFile('tl_settings');
    }

    public function loadPalette($dc)
    {
        $paletteManipulator = PaletteManipulator::create();

        $paletteManipulator->addLegend('gateway_legend', 'title_legend');

        // Add the "email_overrideSmtp" field in Contao <4.10
        if (version_compare(VERSION, '4.10', '<')) {
            $paletteManipulator
                ->addField('email_overrideSmtp', 'gateway_legend', PaletteManipulator::POSITION_APPEND)
                ->applyToPalette('email', 'tl_nc_gateway')
            ;
            return;
        }

        // Show the legacy field "email_overrideSmtp" as long as it is configured
        $gatewayModel = Gateway::findByPk($dc->id);
        if (null !== $gatewayModel && 'email' === $gatewayModel->type && $gatewayModel->email_overrideSmtp) {
            $paletteManipulator
                ->addField('email_overrideSmtp', 'gateway_legend', PaletteManipulator::POSITION_APPEND)
                ->addField('mailerTransport', 'gateway_legend', PaletteManipulator::POSITION_APPEND)
                ->applyToPalette('email', 'tl_nc_gateway')
            ;
            return;
        }

        // Just show the "mailerTransport" field instead of the "email_overrideSmtp"
        $paletteManipulator
            ->addField('mailerTransport', 'gateway_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('email', 'tl_nc_gateway')
        ;
    }

    /**
     * Validate the queue delay
     *
     * @param string $value
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function validateQueueDelay($value)
    {
        if ($value && strtotime($value) === false) {
            throw new \InvalidArgumentException(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $value));
        }

        return $value;
    }

    /**
     * Check the FTP connection
     *
     * @param \DataContainer $dc
     */
    public function checkFileServerConnection(\DataContainer $dc)
    {
        if ('ftp' !== $dc->activeRecord->type || 'ftp' !== $dc->activeRecord->file_connection) {
            return;
        }

        // Try to connect
        if (($ftp = @ftp_connect($dc->activeRecord->file_host, (int) ($dc->activeRecord->file_port ?: 21), 5)) === false) {
            \Message::addError($GLOBALS['TL_LANG']['tl_nc_gateway']['ftp_error_connect']);

            return;
        }

        // Try to login
        if (false === @ftp_login($ftp, $dc->activeRecord->file_username, $dc->activeRecord->file_password)) {
            @ftp_close($ftp);
            \Message::addError($GLOBALS['TL_LANG']['tl_nc_gateway']['ftp_error_login']);

            return;
        }

        \Message::addConfirmation($GLOBALS['TL_LANG']['tl_nc_gateway']['ftp_confirm']);
    }

    /**
     * Gets the back end list label
     *
     * @param array          $row
     * @param string         $label
     * @param \DataContainer $dc
     * @param array          $args
     *
     * @return string
     */
    public function executeLabelCallback($row, $label, \DataContainer $dc, $args)
    {
        $model = Gateway::findByPk($row['id']);
        $gateway = $model->getGateway();

        if ($gateway instanceof LabelCallbackInterface) {

            return $gateway->getLabel($row, $label,$dc, $args);
        }

        return $label;
    }

    /**
     * Gets the cron job explanation
     *
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function queueCronjobExplanation(\DataContainer $dc)
    {
        return sprintf('<div style="color: #4b85ba;
            background: #eff5fa;
            padding: 10px;
            border-radius: 3px;">%s</div>',
            str_replace('{gateway_id}', $dc->id, $GLOBALS['TL_LANG']['tl_nc_gateway']['queueCronjobExplanation'])
        );
    }
}
