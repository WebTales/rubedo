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
use Rubedo\Services\Manager;

/**
 * Controller Rendering TinyUrl accessed resources
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class TinyController extends Zend_Controller_Action
{
    
    /*
     * (non-PHPdoc) @see Zend_Controller_Action::init()
     */
    public function init ()
    {
        $this->tinyUrlService = Manager::getService('TinyUrl');
    }

    function indexAction ()
    {
        $tinyKey = $this->getParam('tk');
        if (! $tinyKey) {
            throw new \Rubedo\Exceptions\User('No tiny URL given.', "Exception26");
        } else {
            $tinyUrlObj = $this->tinyUrlService->findById($tinyKey);
            if (! $tinyUrlObj) {
                throw new \Rubedo\Exceptions\User('Invalid URL key.', "Exception27");
            }
        }
        
        if (isset($tinyUrlObj['url'])) {
            $this->_redirect($tinyUrlObj['url']);
        } else {
            $controller = $tinyUrlObj['controller'];
            $action = $tinyUrlObj['action'];
            $module = $tinyUrlObj['module'];
            $params = $tinyUrlObj['params'];
            $params['tk'] = false;
            $this->_forward($action, $controller, $module, $params);
        }
    }
}
