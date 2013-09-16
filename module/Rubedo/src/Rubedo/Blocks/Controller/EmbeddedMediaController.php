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
use Alb\OEmbed;
use Rubedo\Services\Cache;

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class EmbeddedMediaController extends AbstractController
{

    /**
     * Default Action
     */
    public function indexAction ()
    {
        $blockConfig = $this->params()->fromQuery('block-config', array());
        
        if (isset($blockConfig['url']) && $blockConfig["url"] != "") {
            
            $oembedParams['url'] = $blockConfig['url'];
            
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
            
            $output = $this->params()->fromQuery();
            $output['item'] = $item;
        } else {
            
            $output = array();
        }
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/oembed.html.twig");
        $css = array();
        $js = array();
        
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
