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

    function indexAction()
    {
        $now = Manager::getService('CurrentTime')->getCurrentTime();
        
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
                throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
            }
            
            $filePath = sys_get_temp_dir() . '/' . $fileId;
            if (! is_file($filePath) || $now - filemtime($filePath) > 7 * 24 * 3600) {
                $obj->write($filePath);
            }
            $meta = $obj->file;
            $filename = $meta['filename'];
        }
        if ($filePath) {
            $filename = isset($filename) ? $filename : basename($filePath);
            $nameSegment = explode('.', $filename);
            $extension = array_pop($nameSegment);
            if (! in_array(strtolower($extension), array(
                'gif',
                'jpg',
                'png',
                'jpeg'
            ))) {
                throw new \Rubedo\Exceptions\Access('Not authorized file extension.', "Exception21");
            }
            
            $type = strtolower($extension);
            $type = ($type == 'jpg') ? 'jpeg' : $type;
            // $gdReturnClassName = 'image' . $type;
            $fileSegment = isset($fileId) ? $fileId : str_replace('/', '_', $filePath);
            $tmpImagePath = sys_get_temp_dir() . '/' . $fileSegment . '_' . (isset($width) ? $width : '') . '_' . (isset($height) ? $height : '') . '_' . (isset($mode) ? $mode : '') . '.' . $type;
            
            if (! is_file($tmpImagePath) || $now - filemtime($tmpImagePath) > 7 * 24 * 3600) {
                
                $imageService = new Rubedo\Image\Image();
                $newImage = $imageService->resizeImage($filePath, $mode, $width, $height, $size);
                
                switch ($type) {
                    case 'jpeg':
                        imagejpeg($newImage, $tmpImagePath, 90);
                        break;
                    case 'gif':
                        imagegif($newImage, $tmpImagePath);
                        break;
                    case 'png':
                        imagepng($newImage, $tmpImagePath, 9, PNG_ALL_FILTERS);
                        break;
                }
                
                imagedestroy($newImage);
            }
            switch ($this->getParam('attachment', null)) {
                case 'download':
                    $forceDownload = true;
                    break;
                default:
                    $forceDownload = false;
                    break;
            }
            
            $this->getResponse()->clearBody();
            $this->getResponse()->clearHeaders();
            if ($forceDownload) {
                $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
            }
            $this->getResponse()->setHeader('Content-Type', 'image/' . $type);
            $this->getResponse()->setHeader('Pragma', 'Public');
            $this->getResponse()->setHeader('Cache-Control', 'public, max-age=' . 24 * 3600, true);
            $this->getResponse()->setHeader('Expires', date(DATE_RFC822, strtotime(" 1 day")), true);
            $this->getResponse()->sendHeaders();
            readfile($tmpImagePath);
            //exit();
        } else {
            throw new \Rubedo\Exceptions\User("No Image Given", "Exception80");
        }
    }

    public function getThumbnailAction()
    {
        $this->_forward('index', 'image', 'default', array(
            'size' => 'thumbnail'
        ));
    }
}
