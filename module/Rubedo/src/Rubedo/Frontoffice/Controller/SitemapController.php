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
    protected $contentsLimit=3000;
    function __construct()
    {
        $this->pageService = Manager::getService('Pages');
        $this->sitesService = Manager::getService('Sites');
        $this->config = Manager::getService('config');
        $this->urlService = Manager::getService('Url');
        $this->contentService = Manager::getService('Contents');
    }
    function indexAction()
    {
        if (isset($this->config['rubedo_config']['apiCache'])&&$this->config['rubedo_config']['apiCache']=="1") {
            $urlKey = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $apiCacheService=Manager::getService("ApiCache");
            $foundCachedApiCall=$apiCacheService->findByCacheId($urlKey);
            if($foundCachedApiCall){
                $content=$foundCachedApiCall["cachedResult"];
                $response = $this->getResponse();
                $headers = $response->getHeaders();
                $headers->addHeaderLine('Content-Type', 'text/xml');
                $response->setContent(utf8_decode($content));
                return $response;
            }
        }
        $part=$this->params()->fromQuery("part", null);
        if($part=="pages"){
            $content=$this->getPagesMap();
        } elseif ($part=="contents"){
            $start=$this->params()->fromQuery("start", 0);
            $limit=$this->params()->fromQuery("limit", $this->contentsLimit);
            $content=$this->getContentsMap($start,$limit);
        } else {
            $content=$this->getIndexMap();
        }
        if (isset($this->config['rubedo_config']['apiCache'])&&$this->config['rubedo_config']['apiCache']=="1") {
            $time = Manager::getService('CurrentTime')->getCurrentTime();
            $newCachedApiCall=array(
                "cacheId"=>$urlKey,
                "cachedResult"=>$content,
                "endpoint"=>"sitemap",
                "expireAt"=>new \MongoDate($time+3600)
            );
            $apiCacheService->upsertByCacheId($newCachedApiCall,$urlKey);
        }
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/xml');
        $response->setContent(utf8_decode($content));
        return $response;
    }
    protected function getIndexMap(){
        $siteName = $_SERVER["HTTP_HOST"];
        $currentSite=$this->sitesService->findByHost($siteName);
        if (!$currentSite){
            throw new \Rubedo\Exceptions\NotFound('Site not found');
        }
        $protocol=isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "https" : "http";
        $contentMaps="";
        if (isset($currentSite["sitemapContentTypes"])&&is_array($currentSite["sitemapContentTypes"])&&count($currentSite["sitemapContentTypes"])>0) {
            $contentsFilter = Filter::factory();
            $contentsFilter->addFilter(Filter::factory('In')->setName('typeId')->setValue($currentSite["sitemapContentTypes"]));
            $contents = $this->contentService->getOnlineList($contentsFilter, null, 0, 1);
            $totalContents=$contents["count"];
            if($totalContents>0){
                $start=0;
                $limit=$this->contentsLimit;
                while ($start<$totalContents){
                    $contentMaps=$contentMaps.'<sitemap>
                      <loc>'.$protocol.'://'.$siteName.'/sitemap.xml?part=contents&amp;start='.$start.'&amp;limit='.$limit.'</loc>
                   </sitemap>';
                    $start=$start+$this->contentsLimit;
                    $limit=$limit+$this->contentsLimit;
                }
            }
        }
        $indexMap='<?xml version="1.0" encoding="UTF-8"?>
                   <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
                   <sitemap>
                      <loc>'.$protocol.'://'.$siteName.'/sitemap.xml?part=pages</loc>
                   </sitemap>
                   '.$contentMaps.'
                   </sitemapindex>';
        return $indexMap;
    }

    protected function getPagesMap(){
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
        $pagesMap = "<?xml version='1.0' encoding='UTF-8'?><urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:xhtml='http://www.w3.org/1999/xhtml'>".$body."</urlset>";
        return $pagesMap;
    }

    protected function getContentsMap($start, $limit){
        $siteName = $_SERVER["HTTP_HOST"];
        $currentSite=$this->sitesService->findByHost($siteName);
        if (!$currentSite){
            throw new \Rubedo\Exceptions\NotFound('Site not found');
        }
        $body = '';
        if (isset($currentSite["sitemapContentTypes"])&&is_array($currentSite["sitemapContentTypes"])&&count($currentSite["sitemapContentTypes"])>0){
            $contentsFilter=Filter::factory();
            $contentsFilter->addFilter(Filter::factory('In')->setName('typeId')->setValue($currentSite["sitemapContentTypes"]));
            $contents=$this->contentService->getOnlineList($contentsFilter,null,(int) $start, (int) $limit);
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

        $contentsMap = "<?xml version='1.0' encoding='UTF-8'?><urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:xhtml='http://www.w3.org/1999/xhtml'>".$body."</urlset>";
        return $contentsMap;
    }

    protected function getPages($pagesTree, $siteName, $pages = array(), $parentUrl = array(),$defaultLanguage, $languages)
    {
        foreach($pagesTree as $page){
            if (empty($page['noIndex']))
            {
                $res = [
                    "altLocs"=>[ ]
                ];
                foreach ($page['i18n'] as $lang => $value) {
                    if ($lang==$defaultLanguage){
                        $res['loc'] = 'http://' . $siteName . '/' . $lang . '/' . (empty($parentUrl[$lang])?'':($parentUrl[$lang].'/')) . $page["i18n"][$lang]['pageURL'];
                    }
                    if (in_array($lang,$languages)){
                        $res['altLocs'][]=[
                            "lang"=>$lang,
                            "loc"=>'http://' . $siteName . '/' . $lang . '/' . (empty($parentUrl[$lang])?'':($parentUrl[$lang].'/')) . $page["i18n"][$lang]['pageURL']
                        ];
                    }
                }
                $res['lastmod'] = date('Y-m-d', $page['lastUpdateTime']);
                $pages[] = $res;
            }
            if (empty($page['noFollow']) && !empty($page['children']))
            {
                $newParentUrl=$parentUrl;
                foreach($page['i18n'] as $lang => $value) {
                    if (in_array($lang,$languages)){
                        if (isset($parentUrl[$lang])){
                            $newParentUrl[$lang]=$newParentUrl[$lang]."/".$page["i18n"][$lang]['pageURL'];
                        } else {
                            $newParentUrl[$lang]=$page["i18n"][$lang]['pageURL'];
                        }

                    }
                }
                $pages = $this->getPages($page['children'], $siteName, $pages, $newParentUrl,$defaultLanguage, $languages);
            }
        }
        return $pages;
    }
}