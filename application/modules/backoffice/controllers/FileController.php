<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

use Rubedo\Mongo\DataAccess, Rubedo\Mongo, Rubedo\Services;

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
class Backoffice_FileController extends Zend_Controller_Action
{

    function indexAction() {

    }

    function putAction() {
        $adapter = new Zend_File_Transfer_Adapter_Http();

        if (!$adapter->receive()) {
            $messages = $adapter->getMessages();
            echo implode("\n", $messages);
        }

        $fileInfo = array_pop($adapter->getFileInfo());

        $fileService = Rubedo\Services\Manager::getService('MongoFileAccess');
        $fileService->init();
        $obj = array('serverFilename' => $fileInfo['tmp_name'], 'filename' => $fileInfo['name']);
        $result = $fileService->create($obj);

        $this->_helper->json($result);

    }

    function listAction() {
        $fileService = Rubedo\Services\Manager::getService('MongoFileAccess');
        $fileService->init();
		$filesArray = $fileService->read();
		$data = $filesArray['data'];
		$files = array();
		foreach($data as $value){
			$metaData = $value->file;
			$files[] = (string) $metaData['_id'];
		}
		$this->view->files = $files;
    }

    function getAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $fileId = $this->getRequest()->getParam('file-id');
		
        if (isset($fileId)) {
            $fileService = Rubedo\Services\Manager::getService('MongoFileAccess');
            $fileService->init();
            $obj = $fileService->findById($fileId);

            $image = $obj->getBytes();

            $this->getResponse()->clearBody();
            $this->getResponse()->setHeader('Content-Type', 'image/jpeg');
            $this->getResponse()->setBody($image);

        }else{
        	throw new Zend_Controller_Exception("No Id Given", 1);
			
        }

    }

}
