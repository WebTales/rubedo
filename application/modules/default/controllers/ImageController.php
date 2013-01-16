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
class ImageController extends Zend_Controller_Action
{

    function indexAction ()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $fileId = $this->getRequest()->getParam('file-id');
        
        $size = $this->getParam('size', 'custom');
        if ($size == "custom") {
            $width = $this->getParam('width', null);
            $height = $this->getParam('height', null);
            $mode = $this->getParam('mode', 'morph');
        }
        
        if (isset($fileId)) {
            $fileService = Manager::getService('Images');
            $obj = $fileService->findById($fileId);
            if (! $obj instanceof MongoGridFSFile) {
                throw new Zend_Controller_Exception("No Image Found", 1);
            }
            $image = $obj->getBytes();
            
            $meta = $obj->file;
            $filename = $meta['filename'];
            $nameSegment = explode('.', $filename);
            $extension = array_pop($nameSegment);
            if (! in_array($extension, array(
                'gif',
                'jpg',
                'png',
                'jpeg'
            ))) {
                throw new Zend_Controller_Exception('Not authorized file extension');
            }
            
            $type = strtolower($extension);
            $type = ($type == 'jpg') ? 'jpeg' : $type;
            
            $this->getResponse()->clearBody();
            $this->getResponse()->setHeader('Content-Type', 'image/' . $type);
            $this->getResponse()->setHeader('Cache-Control', 'public, max-age=' . 24 * 3600);
            $this->getResponse()->setHeader('Expires', date(DATE_RFC822, strtotime(" 1 day")));
            $this->getResponse()->setBody($image);
        } else {
            throw new Zend_Controller_Exception("No Id Given", 1);
        }
    }
}
