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
class Blocks_SearchController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        
        // get search parameters
        $params = $this->getRequest()->getParams();
        
        
        $params['pagesize'] = $this->getRequest()->getParam('pagesize', 10);
        
        $site = $this->getRequest()->getParam('site');
		$params['Sites']=$site['text'];
        
        $query = \Rubedo\Services\Manager::getService('ElasticDataSearch');
        $query->init();
        
        $search = $query->search($params);
        $elasticaResultSet = $search["resultSet"];
        $filters = $search["filters"];
        
        // Get total hits
        $nbresults = $elasticaResultSet->getTotalHits();
        if ($params['pagesize'] != "all") {
            $pagecount = intval($nbresults / $params['pagesize']) + 1;
        } else {
            $pagecount = 1;
        }
        
        // Get facets
        $elasticaFacets = $elasticaResultSet->getFacets();
        
        //do not show selected values
        foreach ($elasticaFacets as $name => $facet){
            if(!isset($facet['terms'])){
                continue;
            }
            $facetParam = $this->getRequest()->getParam($name,array());
            
            foreach($facet['terms'] as $key => $term){
                
                if(in_array($term['term'], $facetParam)){
                    unset($elasticaFacets[$name]['terms'][$key]);
                }
            }
        }

        // Get results
        $elasticaResults = $elasticaResultSet->getResults();
        $results = array();
        
        foreach ($elasticaResults as $result) {
            
            $data = $result->getData();
            $resultType = $result->getType();
            $id = $result->getId();
            
            $score = $result->getScore();
            
            if (! is_float($score))
                $score = 1;
            
            $score = round($score * 100);
            $url = "#";
            
            $results[] = array(
                'id' => $id,
                'url' => $url,
                'score' => $score,
                'title' => $data['text'],
                'summary' => $data['summary'],
                'author' => $data['author'],
                'type' => $data['contentType'],
                'lastUpdateTime' => $data['lastUpdateTime']
            );
        }
        
        $output = $params;
        
        $output['results'] = $results;
        $output['nbresults'] = $nbresults;
        $output['pagecount'] = $pagecount;
        $output['facets'] = $elasticaFacets;
        $output['filters'] = $filters;
        

        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/search.html.twig");
        
        $css = array();
        $js = array();
        
        $this->_sendResponse($output, $template, $css, $js);
    }
}
