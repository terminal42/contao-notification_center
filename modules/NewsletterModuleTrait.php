<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace Contao;


trait NewsletterModuleTrait
{
    protected function setCustomTemplate()
    {
        if ($this->nl_template) {
            $this->Template = new \FrontendTemplate($this->nl_template);
            $this->Template->setData($this->arrData);
        }
    }

    /**
     * @return Widget|null
     */
    protected function createCaptchaWidgetIfEnabled()
    {
        if ($this->disableCaptcha) {
            return null;
        }

        $arrField = [
            'name'      => 'subscribe_' . $this->id,
            'label'     => $GLOBALS['TL_LANG']['MSC']['securityQuestion'],
            'inputType' => 'captcha',
            'eval'      => ['mandatory' => true]
        ];

        return new \FormCaptcha(\FormCaptcha::getAttributesFromDca($arrField, $arrField['name']));
    }

    /**
     * @param $strFormId
     * @param $objCaptchaWidget
     * @param $strCallback
     */
    protected function processForm($strFormId, $objCaptchaWidget, $strCallback)
    {
        if (\Input::post('FORM_SUBMIT') == $strFormId)
        {
            $varSubmitted = $this->validateForm($objCaptchaWidget);

            if ($varSubmitted !== false)
            {
                \call_user_func_array([$this, $strCallback], $varSubmitted);
            }
        }
    }

    protected function compileChannels()
    {
        $arrChannels = array();
        $objChannel = \NewsletterChannelModel::findByIds($this->nl_channels);

        // Get the titles
        if ($objChannel !== null)
        {
            while ($objChannel->next())
            {
                $arrChannels[$objChannel->id] = $objChannel->title;
            }
        }

        return $arrChannels;
    }

    protected function redirectToJumpToPage()
    {
        // Redirect to the jumpTo page
        if ($this->jumpTo && ($objTarget = $this->objModel->getRelated('jumpTo')) instanceof PageModel)
        {
            /** @var PageModel $objTarget */
            $this->redirect($objTarget->getFrontendUrl());
        }
    }
}
