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

Use Rubedo\Services\Manager;


/**
 * Back Office Default Controller
 *
 * Invoked when calling /backoffice URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class IndexController extends AbstractExtLoaderController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $this->_auth = Manager::getService('Authentication');
        
        if (! $this->_auth->getIdentity()) {
            $redirectParams = array('action'=>'index','controller'=>'login');
            $backofficeUrl = $this->url()->fromRoute(null,$redirectParams);
            
            if ($this->params()->fromQuery('content')) {
                $redirectParams['content'] = $this->params()->fromQuery('content');
            }
            return $this->redirect()->toRoute(null,$redirectParams);
        }
        
        if (! Manager::getService('Acl')->hasAccess('ui.backoffice')) {
            $redirectParams = array('action'=>'confirm-logout','controller'=>'logout');
            return $this->redirect()->toRoute(null,$redirectParams);
        }
        
        return $this->loadExtApps();
    }
}

