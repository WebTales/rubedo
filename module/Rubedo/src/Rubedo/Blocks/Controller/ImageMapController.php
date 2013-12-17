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
use Zend\Json\Json;

/**
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 */
class ImageMapController extends AbstractController
{

    /**
     * Default Action, return the image map after creating HTML from JSON
     */
    public function indexAction ()
    {
        $blockConfig = $this->params()->fromQuery('block-config', array());
        
        $output = $this->params()->fromQuery();
        $output['image'] = $blockConfig['image'];
        if (! isset($output['image'])) {
            return $this->_sendResponse(array(), "block.html.twig");
        }
        $output['map'] = Json::decode($blockConfig['map'], Json::TYPE_ARRAY);
        foreach ($output['map'] as &$mapElement) {
            if ($mapElement['type'] == "polygon") {
                $mapElement['type'] = "poly";
            }
            if ($mapElement['type'] == "rect") {
                $mapElement['params']['x1'] = $mapElement['params']['x'] + $mapElement['params']['width'];
                $mapElement['params']['y1'] = $mapElement['params']['y'] + $mapElement['params']['height'];
                $mapElement['coords'] = $mapElement['params']['x'] . "," . $mapElement['params']['y'] . "," . $mapElement['params']['x1'] . "," . $mapElement['params']['y1'];
            } else {
                $mapElement['coords'] = implode(",", $mapElement['params']);
            }
        }
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/imageMap.html.twig");
        
        $css = array();
        $js = array(
            "/components/stowball/jQuery-rwdImageMaps/jquery.rwdImageMaps.min.js",
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/imagemap.js")
        );
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
