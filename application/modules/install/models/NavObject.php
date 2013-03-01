<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

/**
 * Form for DB Config
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Install_Model_NavObject
{
    /**
     * return installer navigation
     * 
     * @return Zend_Navigation
     */
    public static function getNav ()
    {
        $container = new Zend_Navigation();
        
        $page = new Zend_Navigation_Page_Mvc(array(
            'label' => 'Database',
            'action' => 'set-db',
            'controller' => 'index',
            'module' => 'install'
        ));
        $container->addPage($page);
        
        $page = new Zend_Navigation_Page_Mvc(array(
            'label' => 'ElasticSearch',
            'action' => 'set-elastic-search',
            'controller' => 'index',
            'module' => 'install'
        ));
        $container->addPage($page);
        
        $page = new Zend_Navigation_Page_Mvc(array(
            'label' => 'Contents',
            'action' => 'set-db-contents',
            'controller' => 'index',
            'module' => 'install'
        ));
        $container->addPage($page);
        
        $page = new Zend_Navigation_Page_Mvc(array(
            'label' => 'Accounts',
            'action' => 'set-admin',
            'controller' => 'index',
            'module' => 'install'
        ));
        $container->addPage($page);
        
        
        
        $page = new Zend_Navigation_Page_Mvc(array(
            'label' => 'Mailer',
            'action' => 'set-mailer',
            'controller' => 'index',
            'module' => 'install'
        ));
        $container->addPage($page);
        
        $page = new Zend_Navigation_Page_Mvc(array(
            'label' => 'Local domains',
            'action' => 'set-local-domains',
            'controller' => 'index',
            'module' => 'install'
        ));
        $container->addPage($page);
        
        $page = new Zend_Navigation_Page_Mvc(array(
            'label' => 'PHP settings',
            'action' => 'set-php-settings',
            'controller' => 'index',
            'module' => 'install'
        ));
        $container->addPage($page);
        
        
        return $container;
    }
}

