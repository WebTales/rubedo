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
        $filePath = $this->getParam('filepath');
        $size = $this->getParam('size', 'custom');
        if ($size == "custom") {
            $width = $this->getParam('width', null);
            $height = $this->getParam('height', null);
            $mode = $this->getParam('mode', 'morph');
        }
        if ($size == "thumbnail") {
            $width = 100;
            $height = 100;
            $mode = 'crop';
        }
        
        if (isset($fileId)) {
            $fileService = Manager::getService('Images');
            $obj = $fileService->findById($fileId);
            if (! $obj instanceof MongoGridFSFile) {
                throw new Zend_Controller_Exception("No Image Found", 1);
            }
            
            $filePath = sys_get_temp_dir() . '/' . $fileId;
            $isWritten = $obj->write($filePath);
            $meta = $obj->file;
            $filename = $meta['filename'];
        }
        if ($filePath) {
            $filename = isset($filename) ? $filename : basename($filePath);
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
            $gdReturnClassName = 'image' . $type;
            
            $imageService = new Rubedo\Image\Image();
            $newImage = $imageService->resizeImage($filePath, $mode, $width, $height, $size);
            
            $this->getResponse()->clearBody();
            $this->getResponse()->setHeader('Content-Type', 'image/' . $type);
            $this->getResponse()->setHeader('Cache-Control', 'public, max-age=' . 24 * 3600);
            $this->getResponse()->setHeader('Expires', date(DATE_RFC822, strtotime(" 1 day")));
            $this->getResponse()->sendHeaders();
            $gdReturnClassName($newImage);
            // imagedestroy($image);
            imagedestroy($newImage);
            
            die();
        } else {
            throw new Zend_Controller_Exception("No Image Given", 1);
        }
    }
}
