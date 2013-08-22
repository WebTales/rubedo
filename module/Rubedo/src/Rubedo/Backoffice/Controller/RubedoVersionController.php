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
namespace Rubedo\Backoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Version\Version;
use Zend\View\Model\JsonModel;
use \Zend\Version\Version as ZendVersion;

/**
 * Controller sending version information
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class RubedoVersionController extends AbstractActionController
{

    /**
     * Return a json which describe all components of the current Rubedo Instance
     */
    public function indexAction()
    {
        $versionArray = array(
            'ZendFramework' => ZendVersion::VERSION,
            'RubedoVersion' => Version::getVersion(),
            'MongoDB' => Version::getMongoServerVersion(),
            'ElasticSearch' => Version::getESServerVersion(),
            'IsRubedoLatest' => Version::isLatest(),
            'Components' => Version::getComponentsVersion()
        );
        
        return new JsonModel($versionArray);
    }
}
