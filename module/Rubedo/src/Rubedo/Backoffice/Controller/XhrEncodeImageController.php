<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Backoffice\Controller;

use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Controller providing a new MongoId
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class XhrEncodeImageController extends AbstractActionController
{

    /**
     * return a new string of a generated mongoId
     */
    public function encodeImageAction()
    {
        $image = $this->params()->fromFiles('image');
        $path = $image["tmp_name"];
        $finfo = new \finfo(FILEINFO_MIME);
        $type = $finfo->file($path);
        $data = file_get_contents($path);
        $base64 = 'data:' . $type . ';base64,' . base64_encode($data);
        $returnArray=array(
            "success"=>true,
            "base64"=>$base64
        );
        $content = Json::encode($returnArray);
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }
}
