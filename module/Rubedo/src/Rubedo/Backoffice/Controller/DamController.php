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

use Rubedo\Services\Manager;
use Zend\Json\Json;

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
class DamController extends DataAccessController
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

    /**
     * Contain the MIME type
     */
    protected $_mimeType = "";

    public function __construct ()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('Dam');
    }
    
    /*
     * (non-PHPdoc) @see DataAccessController::indexAction()
     */
    public function indexAction ()
    {
        // merge filter and tFilter
        $jsonFilter = $this->params()->fromGet('filter', '[]');
        $jsonTFilter = $this->params()->fromGet('tFilter', '[]');
        $filterArray = Json::decode($jsonFilter,Json::TYPE_ARRAY);
        $tFilterArray = Json::decode($jsonTFilter,Json::TYPE_ARRAY);
        $globalFilterArray = array_merge($tFilterArray, $filterArray);
        
        // call standard method with merge array
        $this->params()->fromGet()->set('filter', Json::encode($globalFilterArray));
        parent::indexAction();
    }

    public function getThumbnailAction ()
    {
        $mediaId = $this->params()->fromGet('id', null);
        if (! $mediaId) {
            throw new \Rubedo\Exceptions\User('no id given', "Exception7");
        }
        $media = $this->_dataService->findById($mediaId);
        if (! $media) {
            throw new \Rubedo\Exceptions\NotFound('no media found', "Exception8");
        }
        $version = $this->params()->fromGet('version',$media['id']);
        $mediaType = Manager::getService('DamTypes')->findById($media['typeId']);
        if (! $mediaType) {
            throw new \Rubedo\Exceptions\Server('unknown media type', "Exception9");
        }
        if ($mediaType['mainFileType'] == 'Image') {
            $this->_forward('get-thumbnail', 'image', 'default', array(
                'file-id' => $media['originalFileId'],
                'version' => $version
            ));
        } else {
            $this->_forward('get-thumbnail', 'file', 'default', array(
                'file-id' => $media['originalFileId'],
                'file-type' => $mediaType['mainFileType'],
                'version' => $version
            ));
        }
    }

    public function getOriginalFileAction ()
    {
        $mediaId = $this->params()->fromGet('id', null);
        if (! $mediaId) {
            throw new \Rubedo\Exceptions\User('no id given', "Exception7");
        }
        $media = $this->_dataService->findById($mediaId);
        if (! $media) {
            throw new \Rubedo\Exceptions\NotFound('no media found', "Exception8");
        }
        $version = $this->params()->fromGet('version',$media['id']);
        $mediaType = Manager::getService('DamTypes')->findById($media['typeId']);
        if (! $mediaType) {
            throw new \Rubedo\Exceptions\Server('unknown media type', "Exception9");
        }
        if ($mediaType['mainFileType'] == 'Image') {
            $this->_forward('index', 'image', 'default', array(
                'file-id' => $media['originalFileId'],
                'version' => $version
            ));
        } else {
            $this->_forward('index', 'file', 'default', array(
                'file-id' => $media['originalFileId'],
                'version' => $version
            ));
        }
    }

    public function createAction ()
    {
        $typeId = $this->params()->fromPost('typeId');
        if (! $typeId) {
            throw new \Rubedo\Exceptions\User('no type ID Given', "Exception3");
        }
        $damType = Manager::getService('DamTypes')->findById($typeId);
        $damDirectory = $this->params()->fromPost('directory','notFiled');
        $nativeLanguage = $this->params()->fromPost('workingLanguage','en');
        if (! $damType) {
            throw new \Rubedo\Exceptions\Server('unknown type', "Exception9");
        }
        $obj['typeId'] = $damType['id'];
        $obj['directory'] = $damDirectory;
        $obj['mainFileType'] = $damType['mainFileType'];
        
        $title = $this->params()->fromPost('title');
        if (! $title) {
            throw new \Rubedo\Exceptions\User('missing title', "Exception10");
        }
        $obj['title'] = $title;
        $obj['fields']['title'] = $title;
        $obj['taxonomy'] = Json::decode($this->params()->fromPost('taxonomy', '[]'));
        
        $workspace = $this->params()->fromPost('writeWorkspace');
        if (! is_null($workspace) && $workspace != "") {
            $obj['writeWorkspace'] = $workspace;
            $obj['fields']['writeWorkspace'] = $workspace;
        }
        
        $targets = Json::decode($this->params()->fromPost()->getParam('targetArray'));
        if (is_array($targets) && count($targets) > 0) {
            $obj['target'] = $targets;
            $obj['fields']['target'] = $targets;
        }
        
        $fields = $damType['fields'];
        
        foreach ($fields as $field) {
            if ($field['cType'] == 'Ext.form.field.File') {
                continue;
            }
            $fieldConfig = $field['config'];
            $name = $fieldConfig['name'];
            $obj['fields'][$name] = $this->params()->fromPost($name);
            if (! $fieldConfig['allowBlank'] && ! $obj['fields'][$name]) {
                throw new \Rubedo\Exceptions\User('Required field missing: %1$s', 'Exception4', $name);
            }
        }
        
        foreach ($fields as $field) {
            if ($field['cType'] !== 'Ext.form.field.File') {
                continue;
            }
            $fieldConfig = $field['config'];
            $name = $fieldConfig['name'];
            
            $uploadResult = $this->_uploadFile($name, $damType['mainFileType']);
            if (! is_array($uploadResult)) {
                $obj['fields'][$name] = $uploadResult;
            } else {
                return $this->_returnJson($uploadResult);
            }
            
            if (! $fieldConfig['allowBlank'] && ! $obj['fields'][$name]) {
                throw new \Rubedo\Exceptions\User('Required field missing: %1$s', 'Exception4', $name);
            }
        }
        
        $uploadResult = $this->_uploadFile('originalFileId', $damType['mainFileType']);
        if (! is_array($uploadResult)) {
            $obj['originalFileId'] = $uploadResult;
        } else {
            return $this->_returnJson($uploadResult);
        }
        
        $obj['Content-Type'] = $this->_mimeType;
        
        if (! $obj['originalFileId']) {
            $this->getResponse()->setStatusCode(500);
            return $this->_returnJson(array(
                'success' => false,
                'msg' => 'no main file uploaded'
            ));
        }
        $obj['nativeLanguage']=$nativeLanguage;
        $obj['i18n']=array();
        $obj['i18n'][$nativeLanguage]=array();
        $obj['i18n'][$nativeLanguage]['fields']=$obj['fields'];
        unset($obj['i18n'][$nativeLanguage]['fields']['writeWorkspace']);
        unset($obj['i18n'][$nativeLanguage]['fields']['target']);
        $returnArray = $this->_dataService->create($obj);
        
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        
        $returnValue = Json::encode($returnArray);
        if ($this->_prettyJson) {
            $returnValue = Json::prettyPrint($returnValue);
        }
        $this->getResponse()->setBody($returnValue);
    }
    /*
     * Method used by Back Office mass uploader for each file
     */
    public function massUploadAction ()
    {
        $typeId = $this->params()->fromPost('typeId');
        if (! $typeId) {
            throw new \Rubedo\Exceptions\User('no type ID Given', "Exception3");
        }
        $damType = Manager::getService('DamTypes')->findById($typeId);
        $nativeLanguage = $this->params()->fromPost('workingLanguage','en');
        if (! $damType) {
            throw new \Rubedo\Exceptions\Server('unknown type', "Exception9");
        }
        $obj = array();
        $damDirectory = $this->params()->fromPost('directory','notFiled');
        $obj['directory'] = $damDirectory;
        $obj['typeId'] = $damType['id'];
        $obj['mainFileType'] = $damType['mainFileType'];
        $obj['fields'] = array();
        $obj['taxonomy'] = array();
        $encodedActiveFacets = $this->params()->fromPost('activeFacets');
        $activeFacets = Json::decode($encodedActiveFacets);
        $applyTaxoFacets = $this->params()->fromPost('applyTaxoFacets', false);
        if (($applyTaxoFacets) && ($applyTaxoFacets != "false")) {
            $obj['taxonomy'] = $activeFacets;
        }
        $workspace = $this->params()->fromPost('writeWorkspace');
        if (! is_null($workspace) && $workspace != "") {
            $obj['writeWorkspace'] = $workspace;
            $obj['fields']['writeWorkspace'] = $workspace;
        }
        $targets = Json::decode($this->params()->fromPost('targetArray'));
        if (is_array($targets) && count($targets) > 0) {
            $obj['target'] = $targets;
            $obj['fields']['target'] = $targets;
        }
        $uploadResult = $this->_uploadFile('file', $damType['mainFileType'], true);
        if ($uploadResult['success']) {
            $properName=explode(".", $uploadResult['data']['text']);
            $obj['title'] = $properName[0];
            $obj['fields']['title'] = $properName[0];
            $obj['originalFileId'] = $uploadResult['data']['id'];
        } else {
            return $this->_returnJson($uploadResult);
        }
        $obj['Content-Type'] = $this->_mimeType;
        if (! $obj['originalFileId']) {
            $this->getResponse()->setStatusCode(500);
            return $this->_returnJson(array(
                'success' => false,
                'msg' => 'no main file uploaded'
            ));
        }
        $obj['nativeLanguage']=$nativeLanguage;
        $obj['i18n']=array();
        $obj['i18n'][$nativeLanguage]=array();
        $obj['i18n'][$nativeLanguage]['fields']=$obj['fields'];
        unset($obj['i18n'][$nativeLanguage]['fields']['writeWorkspace']);
        unset($obj['i18n'][$nativeLanguage]['fields']['target']);
        $returnArray = $this->_dataService->create($obj);
        if (! $returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        
        $returnValue = Json::encode($returnArray);
        $this->getResponse()->setBody($returnValue);
    }

    protected function _uploadFile ($name, $fileType, $returnFullResult = false)
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        
        if (! $adapter->receive($name)) {
            return null;
        }
        
        $filesArray = $adapter->getFileInfo();
        
        $fileInfos = $filesArray[$name];
        
        $mimeType = mime_content_type($fileInfos['tmp_name']);
        
        if ($name == 'originalFileId') {
            $this->_mimeType = $mimeType;
        }
        
        $fileService = Manager::getService('Files');
        
        $fileObj = array(
            'serverFilename' => $fileInfos['tmp_name'],
            'text' => $fileInfos['name'],
            'filename' => $fileInfos['name'],
            'Content-Type' => isset($mimeType) ? $mimeType : $fileInfos['type'],
            'mainFileType' => $fileType
        );
        $result = $fileService->create($fileObj);
        if ((! $result['success']) || ($returnFullResult)) {
            return $result;
        }
        return $result['data']['id'];
    }
}
