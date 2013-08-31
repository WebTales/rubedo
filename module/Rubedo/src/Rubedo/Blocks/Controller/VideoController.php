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
namespace Rubedo\Blocks\Controller;

use Rubedo\Services\Manager;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class VideoController extends AbstractController
{

    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());
        $output = $this->getAllParams();
        $output['videoAutoPlay'] = isset($blockConfig['videoAutoPlay']) ? $blockConfig['videoAutoPlay'] : false;
        $output['videoPreload'] = isset($blockConfig['videoPreload']) ? $blockConfig['videoPreload'] : false;
        $output['videoControls'] = isset($blockConfig['videoControls']) ? $blockConfig['videoControls'] : false;
        $output['videoLoop'] = isset($blockConfig['videoLoop']) ? $blockConfig['videoLoop'] : false;
        $output['videoFile'] = isset($blockConfig['videoFile']) ? $blockConfig['videoFile'] : null;
        $output['videoWidth'] = isset($blockConfig['videoWidth']) ? $blockConfig['videoWidth'] . 'px' : '100%';
        $output['videoHeight'] = isset($blockConfig['videoHeight']) ? $blockConfig['videoHeight'] . 'px' : null;
        $output['videoPoster'] = isset($blockConfig['videoPoster']) ? $blockConfig['videoPoster'] : null;
        $output['alternativeMediaArray'] = array();
        if ($output['videoFile']) {
            $media = Manager::getService('Dam')->findById($output['videoFile']);
            $output['contentType'] = $media['Content-Type'];
            
            $mainFile = Manager::getService('Files')->findById($media['originalFileId']);
            if (! $mainFile instanceof MongoGridFSFile) {
                throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
            }
            $meta = $mainFile->file;
            $filename = $meta['filename'];
            
            $output['extension'] = pathinfo($filename, PATHINFO_EXTENSION);
            
            $output['alt'] = isset($media['fields']['alt']) ? $media['fields']['alt'] : '';
        }
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/video.html.twig");
        
        $css = array();
        $js = array(
            '/components/longtailvideo/jwplayer/jwplayer.js',
            '/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/video.js")
        );
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
