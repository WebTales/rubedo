<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

/**
 * Controller sending version information
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_RubedoVersionController extends Zend_Controller_Action
{

    public function indexAction ()
    {
        $versionArray = array(
            'ZendFramework' => Zend_Version::VERSION,
            'RubedoVersion' => Rubedo\Version\Version::getVersion(),
            'IsRubedoLatest' => Rubedo\Version\Version::isLatest(),
            'Components' => Rubedo\Version\Version::getComponentsVersion()
        );
        
        $this->_helper->json($versionArray);
    }
}
