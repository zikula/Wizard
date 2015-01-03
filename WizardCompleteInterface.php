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

use Symfony\Component\HttpFoundation\Request;

interface WizardCompleteInterface
{
    /**
     * Get the Response (probably RedirectResponse) for this completed Wizard
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response;
     */
    public function getResponse(Request $request);
}