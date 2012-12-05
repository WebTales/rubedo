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
class Backoffice_ImageController extends Zend_Controller_Action
{

    function putAction() {
        $adapter = new Zend_File_Transfer_Adapter_Http();

        if (!$adapter->receive()) {
            $messages = $adapter->getMessages();
            echo implode("\n", $messages);
        }

        $fileInfo = array_pop($adapter->getFileInfo());

        $fileService = Rubedo\Services\Manager::getService('Images');
        $obj = array('serverFilename' => $fileInfo['tmp_name'], 'filename' => $fileInfo['name']);
        $result = $fileService->create($obj);

        $this->_helper->json($result);
    }

    function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $fileId = $this->getRequest()->getParam('file-id');
        $version = $this->getRequest()->getParam('file-version', 1);

        if (isset($fileId)) {
            $fileService = Rubedo\Services\Manager::getService('Images');
            $result = $fileService->destroy(array('id' => $fileId, 'version' => $version));

            $this->_helper->json($result);

        } else {
            throw new Zend_Controller_Exception("No Id Given", 1);

        }
    }

    function getAction() {
        $this->_forward('index', 'image', 'default');
    }

}
