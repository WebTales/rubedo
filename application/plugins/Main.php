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
Use Rubedo\Services\Manager;

/**
 * Plugin
 * to
 * handle
 * preMVC
 * context
 *
 * @author
 *         jbourdin
 * @category
 *           Rubedo
 * @package
 *          Rubedo
 */
class Application_Plugin_Main extends Zend_Controller_Plugin_Abstract
{

    /**
     * Called
     * before
     * an
     * action
     * is
     * dispatched
     * by
     * Zend_Controller_Dispatcher.
     *
     * Apply
     * access
     * right
     * control
     *
     * @param Zend_Controller_Request_Abstract $request            
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $module = $request->getModuleName();
        
        $applicationOptions = \Zend_Controller_Front::getInstance()->getParam('bootstrap')
            ->getApplication()
            ->getOptions();
        
        if ($module != 'install' && (! isset($applicationOptions['installed']) || ! isset($applicationOptions['installed']['status']) || $applicationOptions['installed']['status'] !== 'finished')) {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->gotoSimple('index', 'index', 'install');
        }
        
        $ressourceName = 'execute.controller.' . $controller . '.' . $action . '.' . $module;
        if ($module == 'install') {
            $hasAccess = true;
        } elseif (($module == 'default' || ! isset($module)) && (($action == 'index' && $controller == 'index') || ($action == 'error' && $controller == 'error') || ($action == 'index' && $controller == 'image') || ($action == 'index' && $controller == 'dam'))) {
            $hasAccess = true;
        } else {
            $aclService = Manager::getService('Acl');
            $hasAccess = $aclService->hasAccess($ressourceName);
        }
        
        if (! $hasAccess) {
            throw new \Rubedo\Exceptions\Access('Can\'t access %1$s', "Exception30", $ressourceName);
        }
        
        if ($module != 'backoffice' || $controller != 'xhr-authentication' || $action != 'is-session-expiring') {
            Manager::getService('Authentication')->resetExpirationTime();
        }
        try {
            // ensure  that  a default language is set and migration of contents is done
            $defaultLocale = Manager::getService('Languages')->getDefaultLanguage();
        } catch (Exception $e) {}
        
        if ($module != 'install' && ! isset($defaultLocale)) {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->gotoSimple('define-languages', 'index', 'install');
        }
    }
}
