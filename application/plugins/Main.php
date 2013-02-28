<?php

/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

Use Rubedo\Services\Manager;

/**
 * Plugin to handle preMVC context
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Application_Plugin_Main extends Zend_Controller_Plugin_Abstract
{

    /**
     * Called before an action is dispatched by Zend_Controller_Dispatcher.
     *
     * Apply access right control
     *
     * @param Zend_Controller_Request_Abstract $request            
     * @return void
     */
    public function preDispatch (Zend_Controller_Request_Abstract $request)
    {
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $module = $request->getModuleName();
        
        $ressourceName = 'execute.controller.' . $controller . '.' . $action . '.' . $module;
        if($module =='install'){
            $hasAccess = true;
        }elseif (($module == 'default' || ! isset($module)) && (($action == 'index' && $controller == 'index') || ($action == 'error' && $controller == 'error') || ($action == 'index' && $controller == 'image') || ($action == 'index' && $controller == 'dam'))) {
            $hasAccess = true;
        } else {
            $aclService = Manager::getService('Acl');
            $hasAccess = $aclService->hasAccess($ressourceName);
        }
        
        if (! $hasAccess) {
            throw new \Rubedo\Exceptions\Access("can't access $ressourceName");
        }
        
        if($module !='backoffice' || $controller !='xhr-authentication' || $action !='is-session-expiring'){
            Manager::getService('Authentication')->resetExpirationTime();
        }
    }
}
