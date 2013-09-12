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
class ThemeController extends AbstractActionController
{

    function indexAction()
    {
        $theme = $this->params()->fromRoute('theme');
        $filePath = $this->params()->fromRoute('filepath');
        
        $consolidatedFilePath = Manager::getService('FrontOfficeTemplates')->getFilePath($theme, $filePath);
        
        if (! $consolidatedFilePath) {
            throw new \Rubedo\Exceptions\NotFound('File does not exist');
        }
        
        $extension = pathinfo($consolidatedFilePath, PATHINFO_EXTENSION);
        
        switch ($extension) {
            case 'php':
                throw new \Rubedo\Exceptions\NotFound('File does not exist');
                break;
            case 'js':
                $mimeType = 'application/javascript';
                break;
            case 'css':
                $mimeType = 'text/css';
                break;
            case 'html':
                $mimeType = 'text/html';
                break;
            case 'json':
                $mimeType = 'application/json';
                break;
            default:
                if (class_exists('finfo')) {
                    $finfo = new \finfo(FILEINFO_MIME);
                    $mimeType = $finfo->file($consolidatedFilePath);
                }
                break;
        }
        
        $stream = fopen($consolidatedFilePath, 'r');
        
        $response = new \Zend\Http\Response\Stream();
        
        $response->getHeaders()->addHeaders(array(
            'Content-type' => $mimeType
        // 'Pragma' => 'Public',
        // 'Cache-Control' => 'public, max-age=' . 7 * 24 * 3600,
        // 'Expires' => date(DATE_RFC822, strtotime("7 day"))
                ));
        
        $response->setStream($stream);
        return $response;
    }
}
