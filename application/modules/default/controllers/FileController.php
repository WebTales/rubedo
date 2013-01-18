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
class FileController extends Zend_Controller_Action
{

    function indexAction ()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $fileId = $this->getRequest()->getParam('file-id');
        
        if (isset($fileId)) {
            $fileService = Manager::getService('Files');
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
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME);
                $mimeType = $finfo->file($tmpImagePath);
            } else {
                $type = strtolower($extension);
                switch ($type) {
                    case 'doc':
                    case 'dot':
                        $mimeType = 'application/msword';
                        break;
                    case 'xsl':
                        $mimeType = 'application/msexcel';
                        break;
                    case 'ppt':
                    case 'pps':
                    case 'ppa':
                    case 'pot':
                        $mimeType = 'application/ms-powerpoint';
                        break;
                    case 'xlsx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                        break;
                    case 'xltx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
                        break;
                    case 'potx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.presentationml.template';
                        break;
                    case 'ppsx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
                        break;
                    case 'pptx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                        break;
                    case 'sldx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.presentationml.slide';
                        break;
                    case 'docx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                        break;
                    case 'dotx':
                        $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
                        break;
                    case 'pdf':
                        $mimeType = 'application/pdf';
                        break;
                    case 'txt':
                        $mimeType = 'text/plain';
                        break;
                    default:
                        throw new Exception('unknown file type');
                }
            }
            
            list ($type) = explode(';', $mimeType);
            list ($subtype) = explode('/', $type);
            
            switch ($type) {
                case 'application/pdf':
                    $doNotDownload = true;
                    break;
                default:
                    $doNotDownload = false;
                    break;
            }
            if ($subtype == 'text') {
                $doNotDownload = true;
            }
            
            if ($subtype == 'image') {
                $doNotDownload = true;
            }
            
            $this->getResponse()->clearBody();
            $this->getResponse()->setHeader('Content-Type', $mimeType);
            if ($doNotDownload) {
                $this->getResponse()->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"');
            } else {
                $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
            }
            $this->getResponse()->setHeader('Cache-Control', 'public,
             max-age=' . 24 * 3600);
            $this->getResponse()->setHeader('Expires', date(DATE_RFC822, strtotime(" 1 day")));
            
            $this->getResponse()->sendHeaders();
            
            readfile($tmpImagePath);
            
            die();
        } else {
            throw new Zend_Controller_Exception("No Id Given", 1);
        }
    }
}
