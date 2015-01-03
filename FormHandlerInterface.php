<?php
/**
 * Copyright Zikula Foundation 2014.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT.
 * @package Zikula
 * @author Craig Heydenburg
 *
 * Please see the LICENSE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Component\Wizard;

use Symfony\Component\Form\FormInterface;

interface FormHandlerInterface
{
    /**
     * Returns an instance of a Symfony Form Type
     *
     * @return \Symfony\Component\Form\FormTypeInterface
     */
    public function getFormType();

    /**
     * Handle results of previously validated form
     *
     * @param FormInterface $form
     * @return boolean
     */
    public function handleFormResult(FormInterface $form);
}