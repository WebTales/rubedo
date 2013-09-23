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

Use Rubedo\Services\Manager;
Use Alb\OEmbed;
use Zend\View\Model\JsonModel;
use Rubedo\Services\Cache;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ContentSingleController extends AbstractController
{

    public function indexAction ()
    {
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');
        $site = $this->params()->fromQuery('site');
        $blockConfig = $this->params()->fromQuery('block-config');
        $output["blockConfig"] = $blockConfig;
        
        $mongoId = $this->params()->fromQuery('content-id');
        if (isset($output["blockConfig"]["contentId"])) {
            $mongoId = $output["blockConfig"]["contentId"];
        }
        $frontOfficeTemplatesService = Manager::getService('FrontOfficeTemplates');
        
        if (isset($mongoId) && $mongoId != 0) {
            $content = $this->_dataReader->findById($mongoId, true, false);
            $data = $content['fields'];
            $termsArray = array();
            if (isset($content['taxonomy'])) {
                if (is_array($content['taxonomy'])) {
                    foreach ($content['taxonomy'] as $key => $terms) {
                        if ($key == 'navigation') {
                            continue;
                        }
                        
                        if (! is_array($terms) && is_string($terms)) {
                            $terms = array(
                                $terms
                            );
                        }
                        if (is_array($terms)){
                            foreach ($terms as $term) {
                                $readTerm = Manager::getService('TaxonomyTerms')->getTerm($term);
                                
                                if ($readTerm === null) {
                                    $readTerm = array();
                                }
                                
                                foreach ($readTerm as $key => $value) {
                                    $termsArray[$key][] = $value;
                                }
                            }
                        }
                    }
                }
            }
            $data['terms'] = $termsArray;
            $data["id"] = $mongoId;
            $data['locale'] = Manager::getService('CurrentLocalization')->getCurrentLocalization();
            
            $type = $this->_typeReader->findById($content['typeId'], true, false);
            $cTypeArray = array();
            $CKEConfigArray = array();
            $contentTitlesArray = array();
            $output = $this->params()->fromQuery();
            foreach ($type["fields"] as $value) {
                
                $cTypeArray[$value['config']['name']] = $value;
                if ($value["cType"] == "DCEField") {
                    if (is_array($data[$value['config']['name']])) {
                        $contentTitlesArray[$value['config']['name']] = array();
                        foreach ($data[$value['config']['name']] as $intermedValue) {
                            $intermedContent = $this->_dataReader->findById($intermedValue, true, false);
                            $contentTitlesArray[$value['config']['name']][] = $intermedContent['text'];
                        }
                    } else {
                        if (is_string($data[$value['config']['name']]) && preg_match('/[\dabcdef]{24}/', $data[$value['config']['name']]) == 1) {
                            $intermedContent = $this->_dataReader->findById($data[$value['config']['name']], true, false);
                            $contentTitlesArray[$value['config']['name']] = $intermedContent['text'];
                        }
                    }
                } else 
                    if (($value["cType"] == "CKEField")&&(isset($value["config"]["CKETBConfig"]))) {
                        $CKEConfigArray[$value['config']['name']] = $value["config"]["CKETBConfig"];
                    } else 
                        if ($value["cType"] == "externalMediaField") {
                            $mediaConfig = $data[$value["config"]["name"]];
                            
                            if (isset($mediaConfig['url'])) {
                                
                                $oembedParams['url'] = $mediaConfig['url'];
                                
                                $cache = Cache::getCache('oembed');
            
            $options = array();
            
            if (isset($blockConfig['maxWidth'])) {
                $oembedParams['maxWidth'] = $blockConfig['maxWidth'];
                $options['maxWidth'] = $blockConfig['maxWidth'];
            } else {
                $oembedParams['maxWidth'] = 0;
            }
            
            if (isset($blockConfig['maxHeight'])) {
                $oembedParams['maxHeight'] = $blockConfig['maxHeight'];
                $options['maxHeight'] = $blockConfig['maxHeight'];
            } else {
                $oembedParams['maxHeight'] = 0;
            }
            
            $cacheKey = 'oembed_item_' . md5(serialize($oembedParams));
            $loaded = false;
            $item = $cache->getItem($cacheKey,$loaded);
            
            if (!$loaded) {
                // If the URL come from flickr, we check the URL
                if (stristr($oembedParams['url'], 'www.flickr.com')) {
                    $decomposedUrl = explode("/", $oembedParams['url']);
                    
                    $end = false;
                    
                    // We search the photo identifiant and we remove all parameters after it
                    foreach ($decomposedUrl as $key => $value) {
                        if (is_numeric($value) && strlen($value) === 10) {
                            $end = true;
                            continue;
                        }
                        
                        if ($end) {
                            unset($decomposedUrl[$key]);
                        }
                    }
                    
                    $oembedParams['url'] = implode("/", $decomposedUrl);
                }
                
                $response = OEmbed\Simple::request($oembedParams['url'], $options);
                
                $item['width'] = $oembedParams['maxWidth'];
                $item['height'] = $oembedParams['maxHeight'];
                if (! stristr($oembedParams['url'], 'www.flickr.com')) {
                    $item['html'] = $response->getHtml();
                } else {
                    $raw = $response->getRaw();
                    if ($oembedParams['maxWidth'] > 0) {
                        $width_ratio = $raw->width / $oembedParams['maxWidth'];
                    } else {
                        $width_ratio = 1;
                    }
                    if ($oembedParams['maxHeight'] > 0) {
                        $height_ratio = $raw->height / $oembedParams['maxHeight'];
                    } else {
                        $height_ratio = 1;
                    }
                    
                    $size = "";
                    if ($width_ratio > $height_ratio) {
                        $size = "width='" . $oembedParams['maxWidth'] . "'";
                    }
                    if ($width_ratio < $height_ratio) {
                        $size = "height='" . $oembedParams['maxHeight'] . "'";
                    }
                    $item['html'] = "<img src='" . $raw->url . "' " . $size . "' title='" . $raw->title . "'>";
                }
                
                $cache->setItem($cacheKey,$item);
            }
            
            $output['item'] = $item;
                            }
                        }
            }
            if (isset($type['code']) && ! empty($type['code'])) {
                $templateName = $type['code'] . ".html.twig";
            } else {
                $templateName = preg_replace('#[^a-zA-Z]#', '', $type["type"]);
                $templateName .= ".html.twig";
            }
            $hasCustomLayout=false;
            $customLayoutRows=array();
            if ((isset($type['layouts']))&&(is_array($type['layouts']))){
                foreach ($type['layouts'] as $key => $value) {
                    if (($value['type']=="Detail")&&($value['active'])&&($value['site']==$site['id'])){
                        $hasCustomLayout=true;
                        $customLayoutRows=$value['rows'];
                    }
                }
            }
            $output["data"] = $data;
            $output["customLayoutRows"]=$customLayoutRows;
            $output['activateDisqus'] = isset($type['activateDisqus']) ? $type['activateDisqus'] : false;
            $output["type"] = $cTypeArray;
            $output["CKEFields"] = $CKEConfigArray;
            $output["contentTitles"] = $contentTitlesArray;
            
            $js = array(
                $this->getRequest()->getBasePath() . '/' . $frontOfficeTemplatesService->getFileThemePath("js/rubedo-map.js"),
                $this->getRequest()->getBasePath() . '/' . $frontOfficeTemplatesService->getFileThemePath("js/map.js"),
                $this->getRequest()->getBasePath() . '/' . $frontOfficeTemplatesService->getFileThemePath("js/rating.js")
            );
            
            if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
                $template = $frontOfficeTemplatesService->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
            } else if ($hasCustomLayout) {
                $template = $frontOfficeTemplatesService->getFileThemePath("blocks/single/customLayout.html.twig");
            } else{
                $template = $frontOfficeTemplatesService->getFileThemePath("blocks/single/" . $templateName);
                
                if (! is_file($frontOfficeTemplatesService->getTemplateDir() . '/' . $template)) {
                    $template = $frontOfficeTemplatesService->getFileThemePath("blocks/single/default.html.twig");
                }
            }
        } else {
            $output = array();
            $template = $frontOfficeTemplatesService->getFileThemePath("blocks/single/noContent.html.twig");
            $js = array();
        }
        
        $css = array(
            "/components/jquery/timepicker/jquery.ui.timepicker.css",
            "/components/jquery/jqueryui/themes/base/jquery-ui.css"
        );
        
        return $this->_sendResponse($output, $template, $css, $js);
    }

    public function getContentsAction ()
    {
        $this->_dataReader = Manager::getService('Contents');
        $returnArray = array();
        $data = $this->params()->fromQuery();
        if (isset($data['block']['contentId']) && ! empty($data['block']['contentId'])) {
            $content = $this->_dataReader->findById($data['block']['contentId']);
            $returnArray[] = array(
                'text' => $content['text'],
                'id' => $content['id']
            );
            $returnArray['total'] = count($returnArray);
            $returnArray["success"] = true;
        } else {
            $returnArray = array(
                "success" => false,
                "msg" => "No query found"
            );
        }
        return new JsonModel($returnArray);
    }
}
