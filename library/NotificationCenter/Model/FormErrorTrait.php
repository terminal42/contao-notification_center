<?php

/**
 * notification_center extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    LGPL
 */

namespace NotificationCenter\Model;

use Contao\Form;

trait FormErrorTrait
{
    /**
     * @var Form|null
     */
    protected $form = null;

    /**
     * @param Form|null $form
     *
     * @return self
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return Form|null
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param string $error
     *
     * @return self
     */
    public function addFormError($error = '')
    {
        if (null === $this->form || !method_exists($this->form, 'addError')) {
            return $this;
        }

        if (empty($error)) {
            $error = $GLOBALS['TL_LANG']['ERR']['general'];
        }

        $this->form->addError($error);

        return $this;
    }
}
