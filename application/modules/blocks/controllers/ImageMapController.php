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
Use Rubedo\Services\Manager;

require_once ('AbstractController.php');

/**
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_ImageMapController extends Blocks_AbstractController
{

    /**
     * Default Action, return the image map after creating HTML from JSON
     */
    public function indexAction ()
    {
        $blockConfig = $this->getParam('block-config', array());
        
        $output = $this->getAllParams();
        $output['image'] =$blockConfig['image'];
        $output['map'] =Zend_Json::decode($blockConfig['map']);
        foreach ($output['map'] as &$mapElement) {
          if ($mapElement['type']=="polygon"){
              $mapElement['type']="poly";
          }
          if ($mapElement['type']=="rect"){
            $mapElement['params']['x1']=$mapElement['params']['x']+$mapElement['params']['width'];
            $mapElement['params']['y1']=$mapElement['params']['y']+$mapElement['params']['height'];
            $mapElement['coords']=$mapElement['params']['x'].",".$mapElement['params']['y'].",".$mapElement['params']['x1'].",".$mapElement['params']['y1'];
          } else {
            $mapElement['coords']=implode(",", $mapElement['params']);
          }
           
        }
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/imageMap.html.twig");
        
        $css = array();
        $js = array("/components/stowball/jQuery-rwdImageMaps/jquery.rwdImageMaps.min.js",
            '/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/imagemap.js")
        );
        $this->_sendResponse($output, $template, $css, $js);
    }
}
