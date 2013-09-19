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
use Zend\View\Model\JsonModel;
use Rubedo\Services\Cache;
use Zend\Json\Json;
/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class FlickrGalleryController extends AbstractController
{

    public function indexAction ()
    {
        // $output = $this->_getList();
        $flParams = array();
        $flParams['page'] = $this->params()->fromQuery('page', 1);
        $prefix = $this->params()->fromQuery('prefix');
        $output = $this->params()->fromQuery();
        
        $blockConfig = $this->params()->fromQuery('block-config', array());
        
        if (isset($blockConfig['itemsPerPage'])) {
            $flParams['perPage'] = $blockConfig['itemsPerPage'];
        } else {
            $flParams['perPage'] = 12;
        }
        if (isset($blockConfig['user']) && ! empty($blockConfig['user'])) {
            $flParams['user'] = $blockConfig['user'];
        }
        if (isset($blockConfig['tags']) && ! empty($blockConfig['tags'])) {
            $flParams['tags'] = $blockConfig['tags'];
        }
        if (isset($blockConfig['tagmode']) && ! empty($blockConfig['tagmode'])) {
            $flParams['tag_mode'] = ($blockConfig['tagmode'] == 'ALL') ? 'all' : 'or';
        }
        
        if (! isset($flParams['user']) && ! isset($flParams['tags'])) {
            $output['doNotShow'] = true;
            $this->_sendResponse(array(), "block.html.twig");
            return;
        }
        
        $cache = Cache::getCache('flickr');
        //$cacheKey = 'flickr_items_' . md5(serialize($flParams));
        $cacheKeyCount = 'flickr_items_' . md5('count-' . serialize($flParams));
        $flickrService = new \ZendService\Flickr\Flickr('f902ce3a994e839b5ff2c92d7f945641');
        $loaded=$cache->getItem($cacheKeyCount);
        
        if (! $loaded) {
            if (isset($flParams['user'])) {
                $photosArrayCount = $flickrService->userSearch($flParams['user'], array(
                    'per_page' => 1
                ));
            } elseif (isset($flParams['tags'])) {
                $photosArrayCount = $flickrService->tagSearch($flParams['tags'], array(
                    'per_page' => 1,
                    'tag_mode' => $flParams['tag_mode']
                ));
            } else {
                throw new \Rubedo\Exceptions\User('Need a criteria to display Flickr Contents.', "Exception16");
            }
            //$cache->setItem($cacheKeyCount, $photosArrayCount);
        }
        
        // Get the number of pictures in database
        $allFlickrCount = $photosArrayCount->totalResultsAvailable;
        // Define the maximum number of pages
        $maxPage = (int) ($allFlickrCount / $flParams['perPage']);
        if ($allFlickrCount % $flParams['perPage'] > 0) {
            $maxPage ++;
        }
        
        // Set the page to 1 if the user enter a bad page value in the URL
        if ($flParams['page'] < 1 || $flParams['page'] > $maxPage) {
            $flParams['page'] = 1;
        }
        
        // Defines if the arrows of the carousel are displayed or none
        $next = true;
        $previous = true;
        
        if ($flParams['page'] == $maxPage) {
            $next = false;
        }
        
        if ($flParams['page'] <= 1) {
            $previous = false;
        }
        
        if (isset($flParams['user'])) {
            $output['user'] = $flParams['user'];
        }
        if (isset($flParams['tags'])) {
            $output['tags'] =  Json::encode($flParams['tags']);
        }
        if (isset($flParams['tag_mode'])) {
            $output['tagMode'] = $flParams['tag_mode'];
        }
        $output['pageSize'] = $flParams['perPage'];
        $output['maxPage'] = $maxPage;
        $output['allFlickrCount'] = $allFlickrCount;
        $output['page'] = $flParams['page'];
        $output['prefix'] = $prefix;
        $output['previous'] = $previous;
        $output['next'] = $next;
        
        /**
         * *****************************************************
         */
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/flicker.html.twig");
        
        $css = array();
        $js = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/gallery.js")
        );
        
        return $this->_sendResponse($output, $template, $css, $js);
    }

    public function xhrGetImagesAction ()
    {
        $twigVars = $this->_getList();
        
        $html = Manager::getService('FrontOfficeTemplates')->render('root/blocks/flickr/items.html.twig', $twigVars);
        $data = array(
            'html' => $html
        );
        
        return new JsonModel($data);
    }

    protected function _getList ()
    {
        $flParams = array();
        $flParams['page'] = $this->getParamFromQuery('page', 1);
        $prefix =$this->getParamFromQuery('prefix');
        $output =$this->getParamFromQuery();
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $flParams['perPage'] = $this->getParamFromQuery('itemsPerPage', 12);
            $flParams['user'] = $this->getParamFromQuery('user', null);
            if (empty($flParams['user'])) {
                unset($flParams['user']);
            }
            $flParams['tags'] =$this->getParamFromQuery('tags', null);
            if (empty($flParams['tags'])) {
                unset($flParams['tags']);
            }
            $flParams['tag_mode'] = $this->getParamFromQuery('tagMode', null);
            if (empty($flParams['tag_mode'])) {
                unset($flParams['tag_mode']);
            }
        } else {
            $blockConfig = $this->getParamFromQuery('block-config', array());
            
            if (isset($blockConfig['itemsPerPage'])) {
                $flParams['perPage'] = $blockConfig['itemsPerPage'];
            } else {
                $flParams['perPage'] = 12;
            }
            if (isset($blockConfig['user']) && ! empty($blockConfig['user'])) {
                $flParams['user'] = $blockConfig['user'];
            }
            if (isset($blockConfig['tags']) && ! empty($blockConfig['tags'])) {
                $flParams['tags'] = $blockConfig['tags'];
            }
            if (isset($blockConfig['tagmode']) && ! empty($blockConfig['tagmode'])) {
                $flParams['tag_mode'] = ($blockConfig['tagmode'] == 'ALL') ? 'all' : 'or';
            }
        }
        if (! isset($flParams['user']) && ! isset($flParams['tags'])) {
            $output['doNotShow'] = true;
            return $output;
        }
        $cache = Cache::getCache('flickr');
        $cacheKey = 'flickr_items_' . md5(serialize($flParams));
        $cacheKeyCount = 'flickr_items_' . md5('count-' . serialize($flParams));
        $flickrService = new \ZendService\Flickr\Flickr('f902ce3a994e839b5ff2c92d7f945641');
        
        $loaded=$cache->getItem($cacheKeyCount);
        
        if (! $loaded) {
            if (isset($flParams['user'])) {
                $photosArrayCount = $flickrService->userSearch($flParams['user'], array(
                    'per_page' => 1
                ));
            } elseif (isset($flParams['tags'])) {
                $photosArrayCount = $flickrService->tagSearch($flParams['tags'], array(
                    'per_page' => 1,
                    'tag_mode' => $flParams['tag_mode']
                ));
            } else {
                throw new \Rubedo\Exceptions\User('Need a criteria to display Flickr Contents.', "Exception16");
            }
            //$cache->setItem($cacheKeyCount, $photosArrayCount);
        }
        
        // Get the number of pictures in database
        $allFlickrCount = $photosArrayCount->totalResultsAvailable;
        // Define the maximum number of pages
        $maxPage = (int) ($allFlickrCount / $flParams['perPage']);
        if ($allFlickrCount % $flParams['perPage'] > 0) {
            $maxPage ++;
        }
        
        // Set the page to 1 if the user enter a bad page value in the URL
        if ($flParams['page'] < 1 || $flParams['page'] > $maxPage) {
            $flParams['page'] = 1;
        }
        
        // Defines if the arrows of the carousel are displayed or none
        $next = true;
        $previous = true;
        
        if ($flParams['page'] == $maxPage) {
            $next = false;
        }
        
        if ($flParams['page'] <= 1) {
            $previous = false;
        }
        if (! ($items = $cache->getItem($cacheKey))) {
            // Get the pictures
            if (isset($flParams['user'])) {
                $photosArray = $flickrService->userSearch($flParams['user'], array(
                    'per_page' => $flParams['perPage'],
                    'page' => $flParams['page']
                ));
            } elseif (isset($flParams['tags'])) {
                $photosArray = $flickrService->tagSearch($flParams['tags'], array(
                    'per_page' => $flParams['perPage'],
                    'tag_mode' => $flParams['tag_mode'],
                    'page' => $flParams['page']
                ));
            }
            
            // Send to the view
            $items = array();
            foreach ($photosArray as $photo) {
                $item = array();
                $item['id'] = $photo->id;
                $item['title'] = $photo->title;
                $item['datetaken'] = new \DateTime($photo->datetaken);
                if (isset($photo->Large->uri)) {
                    $item['image'] = $photo->Large->uri;
                } elseif ($photo->Medium->uri) {
                    $item['image'] = $photo->Medium->uri;
                } else {
                    $item['image'] = $photo->Square->uri;
                }
                $item['thumbnail'] = $photo->Square->uri;
                $item['thumbnail_width'] = $photo->Thumbnail->width;
                $item['thumbnail_height'] = $photo->Thumbnail->height;
                $items[] = $item;
            }
            $cache->setItem($cacheKey,$items);
        }
        
        $output['items'] = $items;
        if (isset($flParams['user'])) {
            $output['user'] = $flParams['user'];
        }
        if (isset($flParams['tags'])) {
            $output['tags'] = Json::encode($flParams['tags']);
        }
        if (isset($flParams['tag_mode'])) {
            $output['tagMode'] = $flParams['tag_mode'];
        }
        $output['pageSize'] = $flParams['perPage'];
        $output['maxPage'] = $maxPage;
        $output['allFlickrCount'] = $allFlickrCount;
        $output['page'] = $flParams['page'];
        $output['prefix'] = $prefix;
        $output['previous'] = $previous;
        $output['next'] = $next;
        
        return $output;
    }
}
