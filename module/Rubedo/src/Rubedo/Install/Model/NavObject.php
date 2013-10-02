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
use Rubedo\Services\Manager;

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
        $routeMatch = Manager::getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        $router = Manager::getServiceLocator()->get('Router');
        
        $page = new Mvc(array(
            'label' => 'Database',
            'action' => 'set-db',
            'controller' => 'index',
            'route'=>'install'
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'ElasticSearch',
            'action' => 'set-elastic-search',
            'controller' => 'index',
            'route'=>'install'
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Languages',
            'action' => 'define-languages',
            'controller' => 'index',
            'route'=>'install'
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Contents',
            'action' => 'set-db-contents',
            'controller' => 'index',
            'route'=>'install'
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Accounts',
            'action' => 'set-admin',
            'controller' => 'index',
            'route'=>'install'
        ));
        $container->addPage($page);
        
        
        
        $page = new Mvc(array(
            'label' => 'Mailer',
            'action' => 'set-mailer',
            'controller' => 'index',
            'route'=>'install'
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Local domains',
            'action' => 'set-local-domains',
            'controller' => 'index',
            'route'=>'install'
        ));
        $container->addPage($page);
        
        $page = new Mvc(array(
            'label' => 'Application settings',
            'action' => 'set-php-settings',
            'controller' => 'index',
            'route'=>'install'
        ));
        $container->addPage($page);
        
        foreach ($container as $page){
            $page->setRouteMatch($routeMatch);
            $page->setRouter($router);
        }
        
        return $container;
    }
}

