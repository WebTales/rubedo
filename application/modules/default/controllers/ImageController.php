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
        if ($size == "thumbnail") {
            $width = 150;
            $height = 150;
            $mode = 'crop';
        }
        
        if (isset($fileId)) {
            $fileService = Manager::getService('Images');
            $obj = $fileService->findById($fileId);
            if (! $obj instanceof MongoGridFSFile) {
                throw new Zend_Controller_Exception("No Image Found", 1);
            }
            
            $tmpImagePath = sys_get_temp_dir() . '/' . $fileId;
            $isWritten = $obj->write($tmpImagePath);
            
            $meta = $obj->file;
            $filename = $meta['filename'];
            $nameSegment = explode('.', $filename);
            $extension = array_pop($nameSegment);
            if (! in_array($extension, 
                    array(
                            'gif',
                            'jpg',
                            'png',
                            'jpeg'
                    ))) {
                throw new Zend_Controller_Exception(
                        'Not authorized file extension');
            }
            
            $type = strtolower($extension);
            $type = ($type == 'jpg') ? 'jpeg' : $type;
            
            $gdCreateClassName = 'imagecreatefrom' . $type;
            $gdReturnClassName = 'image' . $type;
            
            $image = $gdCreateClassName($tmpImagePath);
            
            list ($imgWidth, $imgHeight) = getimagesize($tmpImagePath);
            
            $ratio = $imgWidth / $imgHeight;
            if ((is_null($width) || $imgWidth == $width) && (is_null($height) ||
                     ($imgHeight == $height))) {
                $newImage = $image;
            } elseif ($mode == 'morph') {
                $width = isset($width) ? $width : $height * $ratio;
                $height = isset($height) ? $height : $width / $ratio;
                
                $newImage = imagecreatetruecolor($width, $height);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, 
                        $height, $imgWidth, $imgHeight);
            } elseif ($mode == 'boxed') {
                if (is_null($width) || is_null($height)) {
                    $width = isset($width) ? $width : $height * $ratio;
                    $height = isset($height) ? $height : $width / $ratio;
                } else {
                    $newRatio = $width / $height;
                    // which dimension should be modified
                    if ($newRatio > $ratio) {
                        $width = $height * $ratio;
                    } else {
                        $height = $width / $ratio;
                    }
                }
                $newImage = imagecreatetruecolor($width, $height);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, 
                        $height, $imgWidth, $imgHeight);
            } elseif ($mode == 'crop') {
                $width = isset($width) ? $width : $imgWidth;
                $height = isset($height) ? $height : $imgHeight;
                
                $widthCoeff = $width / $imgWidth;
                $heightCoeff = $height / $imgHeight;
                $transformCoeff = max($widthCoeff, $heightCoeff);
                
                $tmpWidth = $transformCoeff * $imgWidth;
                $tmpHeight = $transformCoeff * $imgHeight;
                
                $tmpImage = imagecreatetruecolor($tmpWidth, $tmpHeight);
                imagecopyresampled($tmpImage, $image, 0, 0, 0, 0, $tmpWidth, 
                        $tmpHeight, $imgWidth, $imgHeight);
                
                if ($tmpWidth > $width) {
                    $marginWidth = ($tmpWidth - $width) / 2;
                } else {
                    $marginWidth = 0;
                }
                
                if ($tmpHeight > $height) {
                    $marginHeight = ($tmpHeight - $height) / 2;
                } else {
                    $marginHeight = 0;
                }
                
                $newImage = imagecreatetruecolor($width, $height);
                imagecopy($newImage, $tmpImage, 0, 0, $marginWidth, 
                        $marginHeight, $tmpWidth, $tmpHeight);
                imagedestroy($tmpImage);
            } else {
                throw new Zend_Controller_Exception("unimplemented resize mode", 
                        1);
            }
            
            $this->getResponse()->clearBody();
            $this->getResponse()->setHeader('Content-Type', 'image/' . $type);
            $this->getResponse()->setHeader('Cache-Control', 
                    'public, max-age=' . 24 * 3600);
            $this->getResponse()->setHeader('Expires', 
                    date(DATE_RFC822, strtotime(" 1 day")));
            $this->getResponse()->sendHeaders();
            $gdReturnClassName($newImage);
            imagedestroy($image);
            imagedestroy($newImage);
            
            die();
        } else {
            throw new Zend_Controller_Exception("No Id Given", 1);
        }
    }
}
