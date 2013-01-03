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
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_FlickrGalleryController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $cache = Rubedo\Services\Cache::getCache('flicker');
                
        $blockConfig = $this->getRequest()->getParam('block-config',array());
        
        $flParams['perPage'] = array();
        
        if(isset($blockConfig['itemsPerPage'])){
            $flParams['perPage'] = $blockConfig['itemsPerPage'];
        }else{
            $flParams['perPage'] = 12;
        }
        if(isset($blockConfig['user'])){
            $flParams['user'] = $blockConfig['user'];
        }
        if(isset($blockConfig['tags'])){
            $flParams['tags'] = $blockConfig['tags'];
        }
        if(isset($blockConfig['tagmode'])){
            $flParams['tag_mode'] = ($blockConfig['tagmode']=='ALL')?'all':'or';
        }
        
        
        $cacheKey = 'flickr_items_'.md5(serialize($flParams));
        
        if (! ($items = $cache->load($cacheKey))) {
            $flickrService = new Zend_Service_Flickr('f902ce3a994e839b5ff2c92d7f945641');
            if ($flParams['user']) {
                $photosArray = $flickrService->userSearch($flParams['user'], array(
                    'per_page' => $flParams['perPage']
                ));
            }elseif ($flParams['tags']){
                $photosArray = $flickrService->tagSearch($flParams['tags'], array(
                    'per_page' => $flParams['perPage'],
                    'tag_mode'=>$flParams['tag_mode']
                ));
            }else{
                throw new Zend_Controller_Exception('need a criteria to display Flickr Contents');
            }
            
            $items = array();
            foreach ($photosArray as $photo) {
                $item = array();
                $item['id'] = $photo->id;
                $item['title'] = $photo->title;
                $item['datetaken'] = new DateTime($photo->datetaken);
                $item['image'] = $photo->Large->uri;
                $item['thumbnail'] = $photo->Square->uri;
                $item['thumbnail_width'] = $photo->Thumbnail->width;
                $item['thumbnail_height'] = $photo->Thumbnail->height;
                $item['url'] = $photo->Original->clickUri;
                $items[] = $item;
                // Zend_Debug::dump($photo);die();
            }
            $cache->save($items, $cacheKey,array('flickr'));
        }
        
        $output['items'] = $items;
        
        if (isset($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/flicker.html.twig");
        }
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
}
