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
                throw new \Rubedo\Exceptions\NotFound("No Image Found", 1);
            }
            
            $tmpImagePath = sys_get_temp_dir() . '/' . $fileId;
            $now = Manager::getService('CurrentTime')->getCurrentTime();
            
            if(!is_file($tmpImagePath) || $now - filemtime($tmpImagePath)>7 * 24 * 3600){
                $isWritten = $obj->write($tmpImagePath);
            }
            
            $filelength = filesize($tmpImagePath);
            
            $meta = $obj->file;
            $filename = $meta['filename'];
            $nameSegment = explode('.', $filename);
            $extension = array_pop($nameSegment);
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
            
            $seekStart=0;
            $seekEnd=-1;
            if(isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
            
                $seekRange = isset($HTTP_SERVER_VARS['HTTP_RANGE']) ? substr($HTTP_SERVER_VARS['HTTP_RANGE'] , strlen('bytes=')) : substr($_SERVER['HTTP_RANGE'] , strlen('bytes='));
                $range=explode('-',$seekRange);
            
                if($range[0] > 0) {$seekStart = intval($range[0]); }
            
                $seekEnd = ($range[1] > 0) ? intval($range[1]) : -1;
            }

            $this->getResponse()->clearBody();
            $this->getResponse()->clearHeaders();
            if(strpos($mimeType,'video')!==false){
               list($mimeType)  = explode(';',$mimeType);
            }
            $this->getResponse()->setHeader('Content-Type', $mimeType,true);
            
            if ($doNotDownload) {
                $this->getResponse()->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"',true);
            } else {
                $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"',true);
            }
            
            $this->getResponse()->setHeader('Cache-Control', 'public, max-age=' . 7 * 24 * 3600,true);
            $this->getResponse()->setHeader('Expires', date(DATE_RFC822, strtotime(" 7 day")),true);

            if($seekStart >= 0 && $seekEnd > 0){
                $this->getResponse()->setHeader('Content-Length',$filelength-$seekStart,true);
                $this->getResponse()->setHeader('Content-Range',"bytes $seekStart-$seekEnd/$filelength",true);
                $this->getResponse()->setHeader('Accept-Ranges',"0-$filelength",true);
                $this->getResponse()->setRawHeader('HTTP/1.1 206 Partial Content');
                $this->getResponse()->setHttpResponseCode(206);
                $this->getResponse()->setHeader('Status','206 Partial Content');
                $this->getResponse()->sendHeaders();
                $fo = fopen($tmpImagePath, 'rb');
                $bufferSize = 1024*8;
                $currentByte = $seekStart;
                fseek($fo, $seekStart);
                ob_start();
                while($currentByte < $seekEnd){
                    $actualBuffer=($seekEnd - $currentByte > $bufferSize)?$bufferSize:$seekEnd - $currentByte;
                    echo fread($fo, $actualBuffer);
                    $currentByte +=$actualBuffer;
                    ob_flush();
                }
                ob_end_clean;
                
                
                fclose($fo);
            }elseif($seekStart > 0 && $seekEnd == -1){
                $this->getResponse()->setHeader('Content-Length',$filelength-$seekStart,true);
                $this->getResponse()->setHeader('Content-Range',"bytes $seekStart-$filelength/$filelength",true);
                $this->getResponse()->setHeader('Accept-Ranges',"0-$filelength",true);
                $this->getResponse()->setRawHeader('HTTP/1.1 206 Partial Content');
                $this->getResponse()->setHttpResponseCode(206);
                $this->getResponse()->setHeader('Status','206 Partial Content');
                $this->getResponse()->sendHeaders();
                $fo = fopen($tmpImagePath, 'rb');
                
                fseek($fo, $seekStart);
                fpassthru($fo);
                fclose($fo);
            }else{
                $this->getResponse()->setHeader('Content-Length',$filelength);
                $this->getResponse()->sendHeaders();
                readfile($tmpImagePath);
            }
            
            
            exit;
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", 1);
        }
    }

    public function getThumbnailAction ()
    {
        $this->_forward('index', 'image', 'default', array(
            'size' => 'thumbnail',
            'file-id' => null,
            'filepath' => realpath(APPLICATION_PATH . '/../public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/attach_document.png')
        ));
    }
}
