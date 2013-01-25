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
use Rubedo\Services\Manager;

/**
 * Controller providing access to images in gridFS
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
class DamController extends Zend_Controller_Action
{

    function indexAction ()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $mediaId = $this->getRequest()->getParam('media-id');
        
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
    
    public function getThumbnailAction ()
    {
        $mediaId = $this->getParam('media-id', null);
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
            $this->_forward('get-thumbnail', 'image', 'default', array(
                'file-id' => $media['originalFileId']
            ));
        } else {
            $this->_forward('get-thumbnail', 'file', 'default', array(
                'file-id' => $media['originalFileId']
            ));
        }
    }
}
