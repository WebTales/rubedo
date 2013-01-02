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
        $flickrService = new Zend_Service_Flickr('f902ce3a994e839b5ff2c92d7f945641');
    
        $photosArray = $flickrService->userSearch('croixrougedeparis');
        $items = array();
        foreach ($photosArray as $photo){
            $item = array();
            $item['id']=$photo->id;
            $item['title']=$photo->title;
            $item['datetaken']=new DateTime($photo->datetaken);
            $item['image']=$photo->Large->uri;
            $item['thumbnail']=$photo->Thumbnail->uri;
            $item['thumbnail_width']=$photo->Thumbnail->width;
            $item['thumbnail_height']=$photo->Thumbnail->height;
            $item['url']=$photo->Original->clickUri;
            $items[]=$item;
            //Zend_Debug::dump($photo);die();
        }
        
        $output['items']=$items;
        
        
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
