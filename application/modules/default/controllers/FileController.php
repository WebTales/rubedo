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
class FileController extends Zend_Controller_Action
{

    function indexAction ()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $fileId = $this->getRequest()->getParam('file-id');
        $version = $this->getParam('version',1);
        
        if (isset($fileId)) {
            
            $fileService = Manager::getService('Files');
            $obj = $fileService->findById($fileId);
            if (! $obj instanceof MongoGridFSFile) {
                throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
            }
            
            $tmpImagePath = sys_get_temp_dir() . '/' . $fileId. '_' . $version;
            $now = Manager::getService('CurrentTime')->getCurrentTime();
            
            if (! is_file($tmpImagePath) || $now - filemtime($tmpImagePath) > 7 * 24 * 3600) {
                $obj->write($tmpImagePath);
            }
            
            $filelength = filesize($tmpImagePath);
            $lastByte = (string) $filelength - 1;
            
            $meta = $obj->file;
            $filename = $meta['filename'];
            
            if (class_exists('finfo')) {
                $finfo = new finfo(FILEINFO_MIME);
                $mimeType = $finfo->file($tmpImagePath);
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
            
            switch ($this->getParam('attachment', null)) {
                case 'download':
                    $doNotDownload = false;
                    break;
                case 'inline':
                    $doNotDownload = false;
                    break;
                default:
                    break;
            }
            
            $seekStart = 0;
            $seekEnd = - 1;
            if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
                
                $seekRange = isset($HTTP_SERVER_VARS['HTTP_RANGE']) ? substr($HTTP_SERVER_VARS['HTTP_RANGE'], strlen('bytes=')) : substr($_SERVER['HTTP_RANGE'], strlen('bytes='));
                $range = explode('-', $seekRange);
                
                if ($range[0] > 0) {
                    $seekStart = intval($range[0]);
                }
                
                $seekEnd = ($range[1] > 0) ? intval($range[1]) : - 1;
            }
            
            // error_log("access par tranche : $filename $seekStart => $seekEnd");
            $this->getResponse()->clearBody();
            $this->getResponse()->clearHeaders();
            if (strpos($mimeType, 'video') !== false) {
                list ($mimeType) = explode(';', $mimeType);
            }
            $this->getResponse()->setHeader('Content-Type', $mimeType, true);
            
            if ($doNotDownload) {
                $this->getResponse()->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"', true);
            } else {
                $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"', true);
            }
            
            // ensure no buffering for memory issues
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            if ($seekStart >= 0 && $seekEnd > 0 && ! ($filelength == $seekEnd - $seekStart)) {
                $this->getResponse()->setHeader('Content-Length', $filelength - $seekStart, true);
                $this->getResponse()->setHeader('Content-Range', "bytes $seekStart-$seekEnd/$filelength", true);
                $this->getResponse()->setHeader('Accept-Ranges', "bytes", true);
                $this->getResponse()->setRawHeader('HTTP/1.1 206 Partial Content');
                $this->getResponse()->setHttpResponseCode(206);
                $this->getResponse()->setHeader('Status', '206 Partial Content');
                $this->getResponse()->sendHeaders();
                $fo = fopen($tmpImagePath, 'rb');
                $bufferSize = 1024 * 200;
                $currentByte = $seekStart;
                fseek($fo, $seekStart);
                
                while ($currentByte <= $seekEnd) {
                    $actualBuffer = ($seekEnd + 1 - $currentByte > $bufferSize) ? $bufferSize : $seekEnd + 1 - $currentByte;
                    echo fread($fo, $actualBuffer);
                    $currentByte += $actualBuffer;
                    flush();
                }
                
                fclose($fo);
            } elseif ($seekStart > 0 && $seekEnd == - 1) {
                $this->getResponse()->setHeader('Content-Length', $filelength - $seekStart, true);
                $this->getResponse()->setHeader('Content-Range', "bytes $seekStart-$lastByte/$filelength", true);
                $this->getResponse()->setHeader('Accept-Ranges', "bytes", true);
                $this->getResponse()->setRawHeader('HTTP/1.1 206 Partial Content');
                $this->getResponse()->setHttpResponseCode(206);
                $this->getResponse()->setHeader('Status', '206 Partial Content');
                $this->getResponse()->sendHeaders();
                $fo = fopen($tmpImagePath, 'rb');
                
                fseek($fo, $seekStart);
                fpassthru($fo);
                fclose($fo);
            } else {
                $this->getResponse()->setHeader('Accept-Ranges', "bytes", true);
                $this->getResponse()->setHeader('Content-Range', "bytes 0-$lastByte/$filelength", true);
                $this->getResponse()->setHeader('Content-Length', $filelength);
                $this->getResponse()->setHeader('Cache-Control', 'public, max-age=' . 7 * 24 * 3600, true);
                $this->getResponse()->setHeader('Expires', date(DATE_RFC822, strtotime(" 7 day")), true);
                $this->getResponse()->sendHeaders();
                readfile($tmpImagePath);
            }
            
            exit();
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", "Exception7");
        }
    }

    public function getThumbnailAction ()
    {
        $iconPath = realpath(APPLICATION_PATH . '/../public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/attach_document.png');
        switch ($this->getParam('file-type')) {
            case 'Audio':
                $iconPath = realpath(APPLICATION_PATH . '/../public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/speaker.png');
                break;
            case 'Video':
                $iconPath = realpath(APPLICATION_PATH . '/../public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/video.png');
                break;
            case 'Animation':
                $iconPath = realpath(APPLICATION_PATH . '/../public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/palette.png');
                break;
            default:
                break;
        }
        
        $this->_forward('index', 'image', 'default', array(
            'size' => 'thumbnail',
            'file-id' => null,
            'filepath' => $iconPath
        ));
    }
}
