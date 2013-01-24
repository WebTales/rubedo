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
Use Alb\OEmbed;

require_once ('AbstractController.php');

/**
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_EmbeddedMediaController extends Blocks_AbstractController
{

    /**
     * Default Action
     */
    public function indexAction ()
    {
                
        $blockConfig = $this->getRequest()->getParam('block-config',array());
        
        if(isset($blockConfig['url'])){
        	$oembedParams['url'] = $blockConfig['url'];
        } else {
			throw new Zend_Controller_Exception('need an url to display embed content');
		}
        $cache = Rubedo\Services\Cache::getCache('oembed');

		$options = array();
		
		if(isset($blockConfig['maxWidth'])){
            $oembedParams['maxWidth'] = $blockConfig['maxWidth'];
			$options['maxWidth'] = $blockConfig['maxWidth'];
        } else {
        	$oembedParams['maxWidth'] = 0;
        }
		
        if(isset($blockConfig['maxHeight'])){
            $oembedParams['maxHeight'] = $blockConfig['maxHeight'];
			$options['maxHeight'] = $blockConfig['maxHeight'];
        } else {
        	$oembedParams['maxHeight'] = 0;
        }

        $cacheKey = 'oembed_item_'.md5(serialize($oembedParams));
		
		if (!($item = $cache->load($cacheKey))) {
			$response = OEmbed\Simple::request($oembedParams['url'], $options);
			
			$item['width'] = $oembedParams['maxWidth'];
			$item['height'] = $oembedParams['maxHeight'];
			if (!stristr($oembedParams['url'],'www.flickr.com')) {
				$item['html'] = $response->getHtml();
			} else {
				$raw= $response->getRaw();
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
				
				$size="";	
				if ($width_ratio>$height_ratio) {
					$size = "width='".$oembedParams['maxWidth']."'";
				}
				if ($width_ratio<$height_ratio) {
					$size = "height='".$oembedParams['maxHeight']."'";
				}
				$item['html'] = "<img src='".$raw->url."' ".$size."' title='".$raw->title."'>";
			}
			
			$cache->save($item, $cacheKey,array('oembed'));
		
		}
		
        $output['item'] = $item;
		
       	$template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/oembed.html.twig");
        $css = array();
        $js = array();
		
        $this->_sendResponse($output, $template, $css, $js);
    }
}
