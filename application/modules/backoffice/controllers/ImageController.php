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
 * Controller providing access control list
 *
 * Receveive Ajax Calls with needed ressources, send true or false for each of them
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_ImageController extends Zend_Controller_Action
{

	/**
	 * Array with the read only actions
	 */
	protected $_readOnlyAction = array('index', 'get', 'get-meta');
	
    /**
     * Disable layout & rendering, set content type to json
     * init the store parameter if transmitted
     *
     * @see Zend_Controller_Action::init()
     */
    public function init() {
        parent::init();
		
		$sessionService = Manager::getService('Session');
		
        // refuse write action not send by POST
        if (!$this->getRequest()->isPost() && !in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)) {
            throw new \Rubedo\Exceptions\Access("You can't call a write action with a GET request");
        } else {
        	if(!in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)){
        		$user = $sessionService->get('user');
        		$token = $this->getRequest()->getParam('token');
				
				if($token !== $user['token']){
					throw new \Rubedo\Exceptions\Access("The token given in the request doesn't match with the token in session");
				}
        	}
        }

    }

    function indexAction() {
        $fileService = Manager::getService('Images');
        $filesArray = $fileService->getList();
        $data = $filesArray['data'];
        $files = array();
        foreach ($data as $value) {
            $metaData = $value->file;
            $metaData['id'] = (string)$metaData['_id'];
            unset($metaData['_id']);
            $files[] = $metaData;
        }
        return $this->_helper->json(array('data' => $files, 'total' => $filesArray['count']));
    }

    function putAction() {
        $adapter = new Zend_File_Transfer_Adapter_Http();

        if (!$adapter->receive()) {
            $messages = $adapter->getMessages();
            echo implode("\n", $messages);
        }

        $fileInfo = array_pop($adapter->getFileInfo());

        $fileService = Manager::getService('Images');
        $obj = array('serverFilename' => $fileInfo['tmp_name'],'text'=>$fileInfo['name'], 'filename' => $fileInfo['name'], 'Content-Type' => $fileInfo['type']);
        $result = $fileService->create($obj);

        $this->_helper->json($result);
    }

    function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $dataJson = $this->getRequest()->getParam('data',Zend_Json::encode(array()));
        $data = Zend_Json::decode($dataJson);
        

        if (isset($data['id'])) {
            $fileService = Manager::getService('Images');
            $result = $fileService->destroy($data);

            $this->_helper->json($result);

        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", 1);

        }
    }

    function getAction() {
        $this->_forward('index', 'image', 'default');
    }
	
	function getMetaAction(){
		
        $fileId = $this->getRequest()->getParam('file-id');

        if (isset($fileId)) {
            $fileService = Manager::getService('Images');
            $obj = $fileService->findById($fileId);
			if(! $obj instanceof MongoGridFSFile){
				throw new \Rubedo\Exceptions\NotFound("No Image Found", 1);
			}
			$this->_helper->json($obj->file);

        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", 1);

        }
	}

}
