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

require_once('DataAccessController.php');
 
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
    public function init(){
		parent::init();
		
		// init the data access service
		$this -> _dataService = Rubedo\Services\Manager::getService('Dam');
	}
	
	public function getThumbnailAction ()
	{
	    $mediaId = $this->getParam('id', null);
	    if (! $mediaId) {
	        throw new Exception('no id given');
	    }
	    $media = $this->_dataService->findById($mediaId);
	    if(!$media){
	        throw new Exception('no media found');
	    }
	    $mediaType = Manager::getService('MediaTypes')->findById($media['typeId']);
	    if(!$mediaType){
	        throw new Exception('unknown media type');
	    }
	    if($mediaType['mainFileType']=='image'){
	        $this->_forward('index','image','default',array('size'=>'thumbnail','file-id'=>$media['originalFile']));
	    }else{
	        die();
	    }
	}
	
	public function getOriginalFileAction ()
	{
	    $mediaId = $this->getParam('id', null);
	    if (! $mediaId) {
	        throw new Exception('no id given');
	    }
	    $media = $this->_dataService->findById($mediaId);
	    if(!$media){
	        throw new Exception('no media found');
	    }
	    $mediaType = Manager::getService('DamTypes')->findById($media['typeId']);
	    if(!$mediaType){
	        throw new Exception('unknown media type');
	    }
	    if($mediaType['mainFileType']=='image'){
	        $this->_forward('index','image','default',array('file-id'=>$media['originalFile']));
	    }else{
	        $this->_forward('index','file','default',array('file-id'=>$media['originalFile']));
	    }
	}
	
	public function createAction(){
	    $adapter = new Zend_File_Transfer_Adapter_Http();
	
	    if (! $adapter->receive()) {
	        throw new Exception(implode("\n", $adapter->getMessages()));
	    }
	
	    $filesArray = $adapter->getFileInfo();
	    $returnArray = array('success'=>false,'msg'=>'not yet implemented');
	    return $this->_returnJson($returnArray);
	}

}