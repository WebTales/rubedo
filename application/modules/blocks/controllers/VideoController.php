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
Use Rubedo\Services\Manager;

require_once ('AbstractController.php');

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_videoController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array()); 
        $site = $this->getParam('site');
        $output = $this->getAllParams();
        //Zend_Debug::dump($blockConfig);die();
        $output['videoAutoPlay'] = isset($blockConfig['videoPlay']) ? $blockConfig['videoPlay'] : false;
        $output['videoPreload'] = isset($blockConfig['videoPreload']) ? $blockConfig['videoPreload'] : false;
		$output['videoControls'] = isset($blockConfig['videoControls']) ? $blockConfig['videoControls'] : true;
		$output['videoLoop'] = isset($blockConfig['videoLoop']) ? $blockConfig['videoLoop'] : false;
        $output['videoFile'] = isset($blockConfig['videoFile']) ? $blockConfig['videoFile'] : null;
        $output['videoWidth'] = isset($blockConfig['videoWidth']) ? $blockConfig['videoWidth'].'px' : '100%';
        $output['videoHeight'] = isset($blockConfig['videoHeight']) ? $blockConfig['videoHeight'].'px' : null;
        $output['videoPoster'] = isset($blockConfig['videoPoster']) ? $blockConfig['videoPoster'] : null;
        $output['alternativeMediaArray']= array();
        if($output['videoFile']){
            $media = Manager::getService('Dam')->findById($output['videoFile']);
            $output['contentType']=$media['Content-Type'];
            
            $mainFile = Manager::getService('Files')->findById($media['originalFileId']);
            if (! $mainFile instanceof MongoGridFSFile) {
                throw new \Rubedo\Exceptions\NotFound("No Image Found", 1);
            }
            $meta = $mainFile->file;
            $filename = $meta['filename'];
            
            $output['extension'] = pathinfo($filename,PATHINFO_EXTENSION);
            
            $output['alt']= isset($media['fields']['alt'])?$media['fields']['alt']:'';
        }

        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/video.html.twig");
        
        $css = array();
        $js = array('/components/longtailvideo/jwplayer/jwplayer.js','/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/video.js"));
        $this->_sendResponse($output, $template, $css, $js);
    }
}
