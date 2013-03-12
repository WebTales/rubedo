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
 * Front End Edition controller
 *
 * @author nduvollet
 * @category Rubedo
 * @package Rubedo
 */
class XhrEditController extends Zend_Controller_Action
{

    /**
     * variable for the Session service
     *
     * @param
     *            Rubedo\Interfaces\User\ISession
     */
    protected $_session;

    /**
     * variable for the Data service
     *
     * @param
     *            Rubedo\Interfaces\User\ISession
     */
    protected $_dataService;

    /**
     * Init the session service
     */
    public function init ()
    {
        $this->_dataService = Rubedo\services\Manager::getService('Contents');
    }

    /**
     * Allow to define the current theme
     */
    public function indexAction ()
    {
        $contentId = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getParam('data');
        if (! empty($contentId['id'])) {
            $contentId = explode("_", $contentId);
            $id = $contentId[0];
            $field = $contentId[1];
            
            $baseData = $this->_dataService->findById($id, false, false);
            if ($baseData["status"] !== 'published') {
                $returnArray['success'] = false;
                $returnArray['msg'] = 'Content already have a draft version';
            } else {
                $baseData['fields'][$field] = $data;
                if ($field == "text") {
                    $baseData['text'] = $data;
                }
                $returnArray = $this->_dataService->update($baseData, array(
                    'safe' => true
                ), true);
                
            }
        } else {
            $returnArray['success'] = false;
            $returnArray['msg'] = 'No content id given.';
        }
        if (! $returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        
        return $this->_helper->json($returnArray);
    }
}
