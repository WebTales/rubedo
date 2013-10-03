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
namespace Rubedo\Frontoffice\Controller;

use Rubedo\Services\Manager;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Controller Rendering TinyUrl accessed resources
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class TinyController extends AbstractActionController
{

    function indexAction()
    {
        $this->tinyUrlService = Manager::getService('TinyUrl');
        $tinyKey = $this->params()->fromQuery('tk');
        if (! $tinyKey) {
            throw new \Rubedo\Exceptions\User('No tiny URL given.', "Exception26");
        } else {
            $tinyUrlObj = $this->tinyUrlService->findById($tinyKey);
            if (! $tinyUrlObj) {
                throw new \Rubedo\Exceptions\User('Invalid URL key.', "Exception27");
            }
        }
        
        if (isset($tinyUrlObj['url'])) {
            return $this->redirect()->toUrl($tinyUrlObj['url']);
        } else {
            $redirectParams = array(
                'action' => $tinyUrlObj['action'],
                'controller' => $tinyUrlObj['controller']
            );
            $options = array('query'=>$tinyUrlObj['params']);
            return $this->redirect()->toRoute('frontoffice/default', $redirectParams,$options);
        }
    }
}
