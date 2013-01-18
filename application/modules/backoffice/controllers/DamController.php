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
require_once ('DataAccessController.php');

Use Rubedo\Services\Manager;

/**
 * Controller providing CRUD API for the Groups JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_DamController extends Backoffice_DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'read-child',
        'tree',
        'clear-orphan-terms',
        'model',
        'get-original-file',
        'get-thumbnail'
    );

    public function init ()
    {
        parent::init();
        
        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('Dam');
    }
    
    

    /* (non-PHPdoc)
     * @see Backoffice_DataAccessController::indexAction()
     */
    public function indexAction ()
    {
        //merge filter and tFilter
        $jsonFilter = $this->getParam('filter',Zend_Json::encode(array()));
        $jsonTFilter = $this->getParam('tFilter',Zend_Json::encode(array()));
        $filterArray = Zend_Json::decode($jsonFilter);
        $tFilterArray = Zend_Json::decode($jsonTFilter);
        $globalFilterArray = array_merge($tFilterArray,$filterArray);
        
        //call standard method with merge array
        $this->getRequest()->setParam('filter', Zend_Json::encode($globalFilterArray));
        parent::indexAction();
        
    }

	public function getThumbnailAction ()
    {
        $mediaId = $this->getParam('id', null);
        if (! $mediaId) {
            throw new Exception('no id given');
        }
        $media = $this->_dataService->findById($mediaId);
        if (! $media) {
            throw new Exception('no media found');
        }
        $mediaType = Manager::getService('DamTypes')->findById($media['typeId']);
        if (! $mediaType) {
            throw new Exception('unknown media type');
        }
        if ($mediaType['mainFileType'] == 'Image') {
            $this->_forward('index', 'image', 'default', array(
                'size' => 'thumbnail',
                'file-id' => $media['originalFileId']
            ));
        } else {
            //$user = Manager::getService('CurrentUser')->getCurrentUser();
            $this->_forward('index', 'image', 'default', array(
                'width' => '100',
                'height'=>'100',
                'file-id'=>null,
                'filepath' => realpath(APPLICATION_PATH . '/../vendor/webtales/rubedo-backoffice-ui/www/resources/icones/'.Manager::getService('Session')->get('iconSet', 'red').'/128x128/attach_document.png')
            ));
        }
    }

    public function getOriginalFileAction ()
    {
        $mediaId = $this->getParam('id', null);
        if (! $mediaId) {
            throw new Exception('no id given');
        }
        $media = $this->_dataService->findById($mediaId);
        if (! $media) {
            throw new Exception('no media found');
        }
        $mediaType = Manager::getService('DamTypes')->findById($media['typeId']);
        if (! $mediaType) {
            throw new Exception('unknown media type');
        }
        if ($mediaType['mainFileType'] == 'Image') {
            $this->_forward('index', 'image', 'default', array(
                'file-id' => $media['originalFileId']
            ));
        } else {
            $this->_forward('index', 'file', 'default', array(
                'file-id' => $media['originalFileId']
            ));
        }
    }

    public function createAction ()
    {
        $typeId = $this->getParam('typeId');
        if (! $typeId) {
            throw new Zend_Controller_Exception('no type ID Given');
        }
        $damType = Manager::getService('DamTypes')->findById($typeId);
        if (! $damType) {
            throw new Zend_Controller_Exception('unknown type');
        }
        $obj['typeId'] = $damType['id'];
        
        $title = $this->getParam('title');
        if (! $title) {
            throw new Zend_Controller_Exception('missing title');
        }
        $obj['title'] = $title;
        
        $fields = $damType['fields'];
        foreach ($fields as $field) {
            $fieldConfig = $field['config'];
            $name = $fieldConfig['name'];
            $obj['fields'][$name] = $this->getParam($name);
            if (! $fieldConfig['allowBlank'] && ! $obj['fields'][$name]) {
                throw new Zend_Controller_Exception('required field missing :' . $name);
            }
        }
        
        $adapter = new Zend_File_Transfer_Adapter_Http();
        
        if (! $adapter->receive()) {
            throw new Exception(implode("\n", $adapter->getMessages()));
        }
        
        $filesArray = $adapter->getFileInfo();
        $originalFileInfos = $filesArray['originalFile'];
        
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME);
            $mimeType = $finfo->file($originalFileInfos['tmp_name']);
        }
        
        list ($type) = explode(';', $mimeType);
        list ($subtype) = explode('/', $type);
        
        if ($subtype == 'image') {
            $fileService = Manager::getService('Images');
        } else {
            $fileService = Manager::getService('Files');
        }
        
        $fileObj = array(
            'serverFilename' => $originalFileInfos['tmp_name'],
            'text' => $originalFileInfos['name'],
            'filename' => $originalFileInfos['name'],
            'Content-Type' => isset($mimeType) ? $mimeType : $originalFileInfos['type']
        );
        $result = $fileService->create($fileObj);
        if (! $result['success']) {
            $this->getResponse()->setHttpResponseCode(500);
            return $this->_returnJson($result);
        }
        
        $obj['originalFileId'] = $result['data']['id'];
        $obj['Content-Type'] = $mimeType;
        
        $returnArray = $this->_dataService->create($obj);
        
        if (! $returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        
        $returnValue = Zend_Json::encode($returnArray);
        if ($this->_prettyJson) {
            $returnValue = Zend_Json::prettyPrint($returnValue);
        }
        $this->getResponse()->setBody($returnValue);
    }
}