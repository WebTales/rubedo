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
        // get query
        $terms = $this->getRequest()->getParam('query');
        
        // get type filter
        $type = $this->getRequest()->getParam('type');
        
        // get lang filter
        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');
        
        // get author filter
        $author = $this->getRequest()->getParam('author');
        
        // get date filter
        $date = $this->getRequest()->getParam('date');
		
        // get taxonomy filter
        $taxonomy = $this->getRequest()->getParam('taxonomy');
        
        // get pager
        $pager = $this->getRequest()->getParam('pager',0);
            
        // get orderBy
        $orderBy = $this->getRequest()->getParam('orderby','_score');
            
        // get page size
        $pageSize = $this->getRequest()->getParam('pagesize',10);

        
        $query = \Rubedo\Services\Manager::getService('ElasticDataSearch');
        $query->init();
        
        $elasticaResultSet = $query->search($terms, $type, $lang, $author, 
                $date, $taxonomy, $pager, $orderBy, $pageSize);
        
        // Get total hits
        $nbResults = $elasticaResultSet->getTotalHits();
        if ($pageSize != "all") {
            $pageCount = intval($nbResults / $pageSize) + 1;
        } else {
            $pageCount = 1;
        }
        
        // Get facets from the result of the search query
        $elasticaFacets = $elasticaResultSet->getFacets();
        
        $elasticaResults = $elasticaResultSet->getResults();
        
        $results = array();
        
        foreach ($elasticaResults as $result) {
            
            $data = $result->getData();
            $resultType = $result->getType();
            // $lang_id = explode('_',$result->getId());
            // $id = $lang_id[1];
            $id = $result->getId();
            
            $score = $result->getScore();
            
            if (! is_float($score))
                $score = 1;
            
            $score = round($score * 100);
            // $url = $data['canonical_url'];
            // if ($url == '') {
            // no canonical url
            // redirect to default detail page
            // $url = '/detail/index/id/'.$id;
            $url = "#";
            // }
            
            $results[] = array(
                    'id' => $id,
                    'url' => $url,
                    'score' => $score,
                    'title' => $data['text'],
                    'abstract' => $data['abstract'],
                    'author' => $data['author'],
                    'type' => $data['contentType'],
                    'lastUpdateTime' => $data['lastUpdateTime']
            );
        }
        
        $output['searchTerms'] = $terms;
        $output['results'] = $results;
        $output['nbResults'] = $nbResults;
        $output['pager'] = $pager;
        $output['pageCount'] = $pageCount;
        $output['pageSize'] = $pageSize;
        $output['orderBy'] = $orderBy;
        
        $output['typeFacets'] = $elasticaFacets['typeFacet']['terms'];
        $output['authorFacets'] = $elasticaFacets['authorFacet']['terms'];
        $output['dateFacets'] = $elasticaFacets['dateFacet']['entries'];
		$output['taxonomyFacets'] = $elasticaFacets['taxonomyTagsFacet']['terms'];
        $output['type'] = $type;
        $output['lang'] = $lang;
        $output['author'] = $author;
        $output['date'] = $date;
		$output['taxonomy'] = $taxonomy;
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath(
                "blocks/search.html.twig");
        
        $css = array();
        $js = array();
		//print_r($output);
		//exit;
        $this->_sendResponse($output, $template, $css, $js);
    }
}
