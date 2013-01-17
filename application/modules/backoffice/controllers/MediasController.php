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
 * Controller providing CRUD API for the Medias JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_MediasController extends Backoffice_DataAccessController
{

    public function init ()
    {
        parent::init();
        
        // init the data access service
        $this->_dataService = Manager::getService('Medias');
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
        $mediaType = Manager::getService('MediaTypes')->findById($media['typeId']);
        if(!$mediaType){
            throw new Exception('unknown media type');
        }
        if($mediaType['mainFileType']=='image'){
            $this->_forward('index','image','default',array('file-id'=>$media['originalFile']));
        }else{
            $this->_forward('index','file','default',array('file-id'=>$media['originalFile']));
        }
    }
    
    
}