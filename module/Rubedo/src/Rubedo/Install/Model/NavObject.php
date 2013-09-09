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
namespace Rubedo\Install\Model;

use Zend\Navigation\Page\Mvc;
use Zend\Navigation\Navigation;


/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class NavObject
{
    /**
     * return installer navigation
     * 
     * @return Zend_Navigation
     */
    public static function getNav ()
    {
        $container = new Navigation();
        
        $page = new Mvc(array(
            'label' => 'Database',
            'action' => 'set-db',
            'controller' => 'Rubedo\Install\Controller\Index',
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'ElasticSearch',
            'action' => 'set-elastic-search',
            'controller' => 'Rubedo\Install\Controller\Index',
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Languages',
            'action' => 'define-languages',
            'controller' => 'Rubedo\Install\Controller\Index',
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Contents',
            'action' => 'set-db-contents',
            'controller' => 'Rubedo\Install\Controller\Index',
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Accounts',
            'action' => 'set-admin',
            'controller' => 'Rubedo\Install\Controller\Index',
        ));
        $container->addPage($page);
        
        
        
        $page = new Mvc(array(
            'label' => 'Mailer',
            'action' => 'set-mailer',
            'controller' => 'Rubedo\Install\Controller\Index',
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Local domains',
            'action' => 'set-local-domains',
            'controller' => 'Rubedo\Install\Controller\Index',
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Application settings',
            'action' => 'set-php-settings',
            'controller' => 'Rubedo\Install\Controller\Index',
        ));
        $container->addPage($page);
        
        
        return $container;
    }
}

