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
 * Receveive Ajax Calls with needed ressources, send true or false for each of
 * them
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_FileController extends Zend_Controller_Action
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'get',
        'get-meta'
    );

    /**
     * Disable layout & rendering, set content type to json
     * init the store parameter if transmitted
     *
     * @see Zend_Controller_Action::init()
     */
    public function init ()
    {
        parent::init();
        
        $sessionService = Manager::getService('Session');
        
        // refuse write action not send by POST
        if (! $this->getRequest()->isPost() && ! in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)) {
            throw new \Rubedo\Exceptions\Access("You can't call a write action with a GET request", "Exception5");
        } else {
            if (! in_array($this->getRequest()->getActionName(), $this->_readOnlyAction)) {
                $user = $sessionService->get('user');
                $token = $this->getRequest()->getParam('token');
                
                if ($token !== $user['token']) {
                    throw new \Rubedo\Exceptions\Access("The token given in the request doesn't match with the token in session", "Exception6");
                }
            }
        }
    }

    public function indexAction ()
    {
        $fileService = Manager::getService('Files');
        $filesArray = $fileService->getList();
        $data = $filesArray['data'];
        $files = array();
        foreach ($data as $value) {
            $metaData = $value->file;
            $metaData['id'] = (string) $metaData['_id'];
            unset($metaData['_id']);
            $files[] = $metaData;
        }
        return $this->_helper->json(array(
            'data' => $files,
            'total' => $filesArray['count']
        ));
    }

    public function putAction ()
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        
        if (! $adapter->receive()) {
            throw new \Rubedo\Exceptions\Server(implode("\n", $adapter->getMessages()));
        }
        
        $files = $adapter->getFileInfo();
        $fileInfo = array_pop($files);
        
        $finfo = new finfo(FILEINFO_MIME);
        $mimeType = $finfo->file($fileInfo['tmp_name']);
        
        $fileService = Manager::getService('Files');

        $obj = array(
            'serverFilename' => $fileInfo['tmp_name'],
            'text' => $fileInfo['name'],
            'filename' => $fileInfo['name'],
            'Content-Type' => $mimeType,
            'mainFileType' => $this->getParam('mainFileType',null)
        );
        $result = $fileService->create($obj);
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        
        $returnValue = Zend_Json::encode($result);
        
        $returnValue = Zend_Json::prettyPrint($returnValue);
        
        $this->getResponse()->setBody($returnValue);
    }

    public function deleteAction ()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $fileId = $this->getRequest()->getParam('file-id');
        $version = $this->getRequest()->getParam('file-version', 1);
        
        if (isset($fileId)) {
            $fileService = Manager::getService('Files');
            $result = $fileService->destroy(array(
                'id' => $fileId,
                'version' => $version
            ));
            
            if ($result['success'] == true) {
                $this->redirect($this->_helper->url('index'));
            }
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", 1);
        }
    }

    public function getAction ()
    {
        $this->_forward('index', 'file', 'default');
    }

    
    
    public function getMetaAction ()
    {
        $fileId = $this->getRequest()->getParam('file-id');
        
        if (isset($fileId)) {
            $fileService = Manager::getService('Files');
            $obj = $fileService->findById($fileId);
            if (! $obj instanceof MongoGridFSFile) {
                throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
            }
            $this->_helper->json($obj->file);
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", "Exception7");
        }
    }

    public function dropAllFilesAction ()
    {
        $fileService = Manager::getService('MongoFileAccess');
        $fileService->init();
        return $this->_helper->json($fileService->drop());
    }
}
