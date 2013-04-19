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
        $this->_dataService = Manager::getService('Contents');
    }

    /**
     * Allow to define the current theme
     */
    public function indexAction ()
    {
        $contentId = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getParam('data');
 
        if (! empty($contentId)) {
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
                ), false);
                
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
    
    public function saveImageAction() {
        $contentId = $this->getParam("contentId", null);
        $newImageId = $this->getParam("newImageId", null);
        $contentField = $this->getParam("field", null);
        
        if($contentId === null || $newImageId === null || $contentField === null){
            throw new \Rubedo\Exceptions\Server("Vous devez fournir l'identifiant du contenu concerné, l'identifiant de la nouvelle image et le champ à mettre à jour en base de donnée");
        }
        
        $content = $this->_dataService->findById($contentId, true, false);
        
        if(!$content) {
            throw new \Rubedo\Exceptions\Server("L'identifiant de contenu n'éxiste pas");
        }
        
        $content['fields'][$contentField] = $newImageId;
        
        $updateResult = $this->_dataService->update($content);
        
        if($updateResult['success']){
            return $this->_helper->json(array("success" => true));
        } else {
            return $this->_helper->json(array("success" => false, "msg" => "An error occured during the update of the content"));
        }
    }
    
    public function saveDateAction() {
        $contentId = $this->getParam("contentId", null);
        $newDate = $this->getParam("newDate", null);
        $contentField = $this->getParam("field", null);
    
        if($contentId === null || $newDate === null || $contentField === null){
            throw new \Rubedo\Exceptions\Server("Vous devez fournir l'identifiant du contenu concerné, la nouvelle date et le champ à mettre à jour en base de donnée");
        }
    
        $content = $this->_dataService->findById($contentId, true, false);
    
        if(!$content) {
            throw new \Rubedo\Exceptions\Server("L'identifiant de contenu n'éxiste pas");
        }
    
        $content['fields'][$contentField] = $newDate;
    
        $updateResult = $this->_dataService->update($content);
    
        if($updateResult['success']){
            return $this->_helper->json(array("success" => true));
        } else {
            return $this->_helper->json(array("success" => false, "msg" => "An error occured during the update of the content"));
        }
    }
    
    public function saveTimeAction() {
        $contentId = $this->getParam("contentId", null);
        $newTime = $this->getParam("newTime", null);
        $contentField = $this->getParam("field", null);
    
        if($contentId === null || $newTime === null || $contentField === null){
            throw new \Rubedo\Exceptions\Server("Vous devez fournir l'identifiant du contenu concerné, la nouvelle heure et le champ à mettre à jour en base de donnée");
        }
    
        $content = $this->_dataService->findById($contentId, true, false);
    
        if(!$content) {
            throw new \Rubedo\Exceptions\Server("L'identifiant de contenu n'éxiste pas");
        }
    
        $content['fields'][$contentField] = $newTime;
    
        $updateResult = $this->_dataService->update($content);
    
        if($updateResult['success']){
            return $this->_helper->json(array("success" => true));
        } else {
            return $this->_helper->json(array("success" => false, "msg" => "An error occured during the update of the content"));
        }
    }
    
    public function saveNumberAction() {
        $contentId = $this->getParam("contentId", null);
        $newNumber = $this->getParam("newNumber", null);
        $contentField = $this->getParam("field", null);
    
        if($contentId === null || $newNumber === null || $contentField === null){
            throw new \Rubedo\Exceptions\Server("Vous devez fournir l'identifiant du contenu concerné, le nouveau nombre et le champ à mettre à jour en base de donnée");
        }
    
        $content = $this->_dataService->findById($contentId, true, false);
    
        if(!$content) {
            throw new \Rubedo\Exceptions\Server("L'identifiant de contenu n'éxiste pas");
        }
    
        $content['fields'][$contentField] = $newNumber;
    
        $updateResult = $this->_dataService->update($content);
    
        if($updateResult['success']){
            return $this->_helper->json(array("success" => true));
        } else {
            return $this->_helper->json(array("success" => false, "msg" => "An error occured during the update of the content"));
        }
    }
}
