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

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

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
class ImageController extends AbstractActionController
{

    function indexAction()
    {
        $fileService = Manager::getService('Images');
        $filesArray = $fileService->getList();
        $data = $filesArray['data'];
        $files = array();
        foreach ($data as $value) {
            $metaData = $value->file;
            $metaData['id'] = (string) $metaData['_id'];
            unset($metaData['_id']);
            $files[] = $metaData;
        }
        return new JsonModel(array(
            'data' => $files,
            'total' => $filesArray['count']
        ));
    }

    function putAction()
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        
        if (! $adapter->receive()) {
            $messages = $adapter->getMessages();
            echo implode("\n", $messages);
        }
        
        $fileInfo = array_pop($adapter->getFileInfo());
        
        $fileService = Manager::getService('Images');
        $obj = array(
            'serverFilename' => $fileInfo['tmp_name'],
            'text' => $fileInfo['name'],
            'filename' => $fileInfo['name'],
            'Content-Type' => $fileInfo['type']
        );
        $result = $fileService->create($obj);
        
        return new JsonModel($result);
    }

    function deleteAction()
    {
        $dataJson = $this->params()->fromPost('data', Json::encode(array()));
        $data = Json::decode($dataJson);
        
        if (isset($data['id'])) {
            $fileService = Manager::getService('Images');
            $result = $fileService->destroy($data);
            
            return new JsonModel($result);
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", "Exception7");
        }
    }

    function getAction()
    {
        $this->_forward('index', 'image', 'default');
    }

    function getMetaAction()
    {
        $fileId = $this->getRequest()->getParam('file-id');
        
        if (isset($fileId)) {
            $fileService = Manager::getService('Images');
            $obj = $fileService->findById($fileId);
            if (! $obj instanceof \MongoGridFSFile) {
                throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
            }
            return new JsonModel($obj->file);
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", "Exception7");
        }
    }
}
