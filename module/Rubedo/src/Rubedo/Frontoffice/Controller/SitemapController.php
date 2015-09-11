<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Frontoffice\Controller;

use WebTales\MongoFilters\Filter;
use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;

/**
 * Controller providing sitemap generation
 *
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 *
 */
class SitemapController extends AbstractActionController
{

    protected $pageService;
    protected $sitesService;
    protected $config;
    protected $urlService;
    protected $contentTypeService;
    protected $contentService;
    function __construct()
    {
        $this->pageService = Manager::getService('Pages');
        $this->sitesService = Manager::getService('Sites');
        $this->config = Manager::getService('config');
        $this->urlService = Manager::getService('Url');
        $this->contentService = Manager::getService('Contents');
        $this->contentTypeService = Manager::getService('ContentTypes');
    }
    function indexAction()
    {
        $siteName = $_SERVER["HTTP_HOST"];
        $currentSite=$this->sitesService->findByHost($siteName);
        if (!$currentSite){
            throw new \Rubedo\Exceptions\NotFound('Site not found');
        }
        $pagesFilter=Filter::factory();
        $pagesFilter->addFilter(Filter::factory("Value")->setName("site")->setValue($currentSite["id"]));
        $pagesTree = $this->pageService->readTree($pagesFilter);
        $pages = $this->getPages($pagesTree, $siteName,[ ],"",$currentSite["defaultLanguage"],$currentSite["languages"]);
        $body = '';
        foreach($pages as $page){
            $body = $body . '<url>' . '<loc>' . str_replace("&","&amp;",$page['loc']) . '</loc>' . '<lastmod>' . $page['lastmod'] . '</lastmod>';
            foreach($page["altLocs"] as $altLoc){
                $body=$body.'<xhtml:link
                 rel="alternate"
                 hreflang="'.$altLoc["lang"].'"
                 href="'.str_replace("&","&amp;",$altLoc['loc']).'"
                     />';
            }
            $body=$body.'</url>';
        }
        if (isset($currentSite["sitemapContentTypes"])&&is_array($currentSite["sitemapContentTypes"])&&count($currentSite["sitemapContentTypes"])>0){
            $contentsFilter=Filter::factory();
            $contentsFilter->addFilter(Filter::factory('In')->setName('typeId')->setValue($currentSite["sitemapContentTypes"]));
            $contents=$this->contentService->getOnlineList($contentsFilter);
            $urlAPIService=Manager::getService("RubedoAPI\\Services\\Router\\Url");
            $contentUrlsArray=[ ];
            foreach($contents["data"] as $content){
                if (isset($content["i18n"][$currentSite["defaultLanguage"]])){
                    $newContentUrl=[
                        "loc"=>'http://' . $siteName .$urlAPIService->displayUrlApi($content, 'canonical', $currentSite, null, $currentSite["defaultLanguage"], null),
                        "lastmod"=> date('Y-m-d', $content['lastUpdateTime']),
                        "altLocs"=>[ ]
                    ];
                    foreach ($content['i18n'] as $lang => $value) {

                        if (in_array($lang,$currentSite["languages"])){
                            $newContentUrl['altLocs'][]=[
                                "lang"=>$lang,
                                "loc"=>'http://' . $siteName .$urlAPIService->displayUrlApi($content, 'canonical', $currentSite, null, $lang, null),
                            ];
                        }
                    }
                    $contentUrlsArray[]=$newContentUrl;
                }
            }
            foreach($contentUrlsArray as $contentUrl){
                $body = $body . '<url>' . '<loc>' . str_replace("&","&amp;",$contentUrl['loc']) . '</loc>' . '<lastmod>' . $contentUrl['lastmod'] . '</lastmod>';
                foreach($contentUrl["altLocs"] as $altLoc){
                    $body=$body.'<xhtml:link
                 rel="alternate"
                 hreflang="'.$altLoc["lang"].'"
                 href="'.str_replace("&","&amp;",$altLoc['loc']).'"
                     />';
                }
                $body=$body.'</url>';
            }
        }
        
        $content = "<?xml version='1.0' encoding='UTF-8'?><urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:xhtml='http://www.w3.org/1999/xhtml'>".$body."</urlset>";
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/xml');
        $response->setContent(utf8_decode($content));
        return $response;
    }
    protected function getPages($pagesTree, $siteName, $pages = array(), $parentUrl = "",$defaultLanguage, $languages)
    {
        foreach($pagesTree as $page){
            if (empty($page['noIndex']))
            {
                $res = [
                    "altLocs"=>[ ]
                ];
                foreach ($page['i18n'] as $lang => $value) {
                    if ($lang==$defaultLanguage){
                        $res['loc'] = 'http://' . $siteName . '/' . $lang . '/' . (empty($parentUrl)?'':($parentUrl.'/')) . $page['pageURL'];
                    }
                    if (in_array($lang,$languages)){
                        $res['altLocs'][]=[
                            "lang"=>$lang,
                            "loc"=>'http://' . $siteName . '/' . $lang . '/' . (empty($parentUrl)?'':($parentUrl.'/')) . $page['pageURL']
                        ];
                    }
                }
                $res['lastmod'] = date('Y-m-d', $page['lastUpdateTime']);
                $pages[] = $res;
            }
            if (empty($page['noFollow']) && !empty($page['children']))
            {
                $pages = $this->getPages($page['children'], $siteName, $pages, $page['pageURL'],$defaultLanguage, $languages);
            }
        }
        return $pages;
    }
}