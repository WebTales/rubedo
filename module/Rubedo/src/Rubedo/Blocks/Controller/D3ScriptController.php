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
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class D3ScriptController extends AbstractController
{
    protected $_option = 'all';
    
    public function indexAction ()
    {
        $blockConfig = $this->params()->fromQuery('block-config', array());
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/d3Script.html.twig");
        $output=array();
        $output["d3Code"]=$blockConfig["d3Code"];
        $output["predefinedFacets"] = isset($blockConfig["predefinedFacets"]) ? $blockConfig["predefinedFacets"] : "{ }" ;
        $output["pageSize"] = isset($blockConfig["pageSize"]) ? $blockConfig["pageSize"] : 5000 ;
        $css = array();
        $js = array();
        return $this->_sendResponse($output, $template, $css, $js);
    }
    
    public function getDataAction ()
    {
        if ($this->getRequest()->isXmlHttpRequest()){
            $this->init();
        }
        // get params
        $params = $this->params()->fromPost();
        if (isset($params['option'])) {
            $this->_option = $params['option'];
        }
        if (isset($params['predefinedFacets'])) {
            $predefParamsArray = Json::decode($params['predefinedFacets'],Json::TYPE_ARRAY);
            if (is_array($predefParamsArray)){
                foreach ($predefParamsArray as $key => $value) {
                    if (! isset($params[$key]) or ! in_array($value, $params[$key]))
                        $params[$key][] = $value;
                }
            }
        }
        $query = Manager::getService('ElasticDataSearch');
        $query->init();
        $results = $query->search($params, $this->_option, false);
        return new JsonModel($results);
        
    }
}
