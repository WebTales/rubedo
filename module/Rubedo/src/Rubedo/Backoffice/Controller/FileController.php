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
 * Receveive Ajax Calls with needed ressources, send true or false for each of
 * them
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class FileController extends AbstractActionController
{

    public function indexAction()
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
        return new JsonModel(array(
            'data' => $files,
            'total' => $filesArray['count']
        ));
    }

    public function putAction()
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        
        if (! $adapter->receive()) {
            throw new \Rubedo\Exceptions\Server(implode("\n", $adapter->getMessages()));
        }
        
        $files = $adapter->getFileInfo();
        $fileInfo = array_pop($files);
        
        $finfo = new \finfo(FILEINFO_MIME);
        $mimeType = $finfo->file($fileInfo['tmp_name']);
        
        $fileService = Manager::getService('Files');
        
        $obj = array(
            'serverFilename' => $fileInfo['tmp_name'],
            'text' => $fileInfo['name'],
            'filename' => $fileInfo['name'],
            'Content-Type' => $mimeType,
            'mainFileType' => $this->getParam('mainFileType', null)
        );
        $result = $fileService->create($obj);
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        
        return new JsonModel($result);
    }

    public function updateAction()
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        
        if (! $adapter->receive('image')) {
            throw new \Rubedo\Exceptions\Server(implode("\n", $adapter->getMessages()));
        }
        
        $filesArray = $adapter->getFileInfo();
        
        $fileInfos = $filesArray['image'];
        
        $mimeType = mime_content_type($fileInfos['tmp_name']);
        
        $fileService = Manager::getService('Files');
        $originalId = $this->getRequest()->getParam("originalId");
        $removeOldResult = $fileService->destroy(array(
            'id' => $originalId,
            'version' => 1
        ));
        $fileObj = array(
            'serverFilename' => $fileInfos['tmp_name'],
            'text' => $fileInfos['name'],
            'filename' => $fileInfos['name'],
            'Content-Type' => isset($mimeType) ? $mimeType : $fileInfos['type'],
            'mainFileType' => 'Image',
            '_id' => new \MongoId($originalId)
        );
        $updateResult = $fileService->create($fileObj);
        
        // trigger deletion of cache : sys_get_temp_dir() . '/' . $fileId . '_'
        $directoryIterator = new \DirectoryIterator(sys_get_temp_dir());
        foreach ($directoryIterator as $file) {
            if ($file->isDot()) {
                continue;
            }
            if (strpos($file->getFilename(), $originalId) === 0) {
                unlink($file->getPathname());
            }
        }
        
        $this->_helper->redirector->gotoUrl('/backoffice/resources/afterPixlr.html');
        // just a test prototype, work in progress on this action
    }

    public function deleteAction()
    {
        
        $fileId = $this->params()->fromPost('file-id');
        $version = $this->params()->fromPost('file-version', 1);
        
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

    public function getAction()
    {
        $this->_forward('index', 'file', 'default');
    }

    public function getMetaAction()
    {
        $fileId = $this->params()->fromQuery('file-id');
        
        if (isset($fileId)) {
            $fileService = Manager::getService('Files');
            $obj = $fileService->findById($fileId);
            if (! $obj instanceof \MongoGridFSFile) {
                throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
            }
            return new JsonModel($obj->file);
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", "Exception7");
        }
    }

    public function dropAllFilesAction()
    {
        $fileService = Manager::getService('MongoFileAccess');
        $fileService->init();
        return new JsonModel($fileService->drop());
    }
}
