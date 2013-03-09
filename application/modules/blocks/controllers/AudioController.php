<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */
Use Rubedo\Services\Manager;

require_once ('AbstractController.php');

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_AudioController extends Blocks_AbstractController
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
        $output['audioAutoPlay'] = isset($blockConfig['audioPlay']) ? $blockConfig['audioPlay'] : false;
        $output['audioPreload'] = isset($blockConfig['audioPreload']) ? $blockConfig['audioPreload'] : false;
		$output['audioControls'] = isset($blockConfig['audioControls']) ? $blockConfig['audioControls'] : true;
		$output['audioLoop'] = isset($blockConfig['audioLoop']) ? $blockConfig['audioLoop'] : false;
        $output['audioFile'] = isset($blockConfig['audioFile']) ? $blockConfig['audioFile'] : null;
        $output['alternativeMediaArray']= array();
        if($output['audioFile']){
            $media = Manager::getService('Dam')->findById($output['audioFile']);
            $output['contentType']=$media['Content-Type'];
            if(isset($media['fields']['alternativeFiles'])){
                if(!is_array($media['fields']['alternativeFiles'])){
                    $media['fields']['alternativeFiles'] = array($media['fields']['alternativeFiles']);
                }
                foreach($media['fields']['alternativeFiles'] as $alternativeFile){
                    $altFile = Manager::getService('Files')->findById($alternativeFile);
                    $meta = $altFile->file;
                    $id = (string)$meta['_id'];
                    list($contentType) = explode(';',$meta['Content-Type']);
                    $output['alternativeMediaArray'][]=array(
                            'id'=> $id,
                            'contentType'=> $contentType
                    );
                }
            }
            $output['alt']= isset($media['fields']['alt'])?$media['fields']['alt']:'Your browser does not support the audio element.';
        }

        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/audio.html.twig");
        
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
