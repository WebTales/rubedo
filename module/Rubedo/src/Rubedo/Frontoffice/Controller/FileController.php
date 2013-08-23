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
namespace Rubedo\Frontoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
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
class FileController extends AbstractActionController
{

    function indexAction()
    {
        $fileId = $this->params()->fromQuery('file-id');
        $version = $this->params()->fromQuery('version', 1);
        
        if (isset($fileId)) {
            
            $fileService = Manager::getService('Files');
            $obj = $fileService->findById($fileId);
            if (! $obj instanceof \MongoGridFSFile) {
                throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
            }
            
            $tmpImagePath = sys_get_temp_dir() . '/' . $fileId . '_' . $version;
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
            
            $response = new \Zend\Http\Response\Stream();
            
            if ($forceDownload) {
                $response->getHeaders()->addHeaders(array(
                    'Content-Disposition' => 'attachment; filename="' . $filename
                ));
            } else {
                $response->getHeaders()->addHeaders(array(
                    'Content-Disposition' => 'inline; filename="' . $filename
                ));
            }
            
            if (strpos($mimeType, 'video') !== false) {
                list ($mimeType) = explode(';', $mimeType);
            }
            $response->getHeaders()->addHeaders(array(
                'Content-Type' => $mimeType
            ));
            $this->getResponse()->setHeader('Content-Type', $mimeType, true);
            
            $stream = fopen($tmpImagePath, 'rb');
            
            if ($seekStart >= 0 && $seekEnd > 0 && ! ($filelength == $seekEnd - $seekStart)) {
                $response->getHeaders()->addHeaders(array(
                    'Content-Length' => $filelength - $seekStart,
                    'Content-Range' => "bytes $seekStart-$seekEnd/$filelength",
                    'Accept-Ranges' => "bytes",
                    'Status' => '206 Partial Content'
                ));
                $response->setStatusCode(206);
                $response->setReasonPhrase('Partial Content');
                $response->setContentLength($seekEnd + 1 - $seekStart);
            } elseif ($seekStart > 0 && $seekEnd == - 1) {
                $response->getHeaders()->addHeaders(array(
                    'Content-Length' => $filelength - $seekStart,
                    'Content-Range' => "bytes $seekStart-$lastByte/$filelength",
                    'Accept-Ranges' => "bytes",
                    'Status' => '206 Partial Content'
                ));
                $response->setStatusCode(206);
                $response->setReasonPhrase('Partial Content');
            } else {
                $response->getHeaders()->addHeaders(array(
                    'Content-Length' => $filelength,
                    'Content-Range' => "bytes 0-/$filelength",
                    'Accept-Ranges',
                    "bytes"
                ));
            }
            if ($seekStart) {
                fseek($stream, $seekStart);
            }
            $response->setStream($stream);
            return $response;
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", "Exception7");
        }
    }

    public function getThumbnailAction()
    {
        $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/attach_document.png');
        switch ($this->getParam('file-type')) {
            case 'Audio':
                $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/speaker.png');
                break;
            case 'Video':
                $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/video.png');
                break;
            case 'Animation':
                $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/palette.png');
                break;
            default:
                break;
        }
        
        $queryString = $this->getRequest()->getQuery();
        $queryString->set('size', 'thumbnail');
        $queryString->set('file-id', null);
        $queryString->set('filepath', $iconPath);
        return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\Image', array(
            'action' => 'index'
        ));
    }
}
