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
    public function indexAction() {
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

        // get pager
        $pager = $this->getRequest()->getParam('pager');
        if ($pager == '')
            $pager = 0;

        // get orderBy
        $orderBy = $this->getRequest()->getParam('orderby');
        if ($orderBy == '')
            $orderBy = "_score";

        // get page size
        $pageSize = $this->getRequest()->getParam('pagesize');
        if ($pageSize == '')
            $pageSize = 10;

        $query = \Rubedo\Services\Manager::getService('ElasticDataSearch');
        $query->init();

        $elasticaResultSet = $query->search($terms, $type, $lang, $author, $date, $pager, $orderBy, $pageSize);

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
            //$lang_id = explode('_',$result->getId());
            //$id = $lang_id[1];
            $id = "0";

            $score = $result->getScore();

            if (!is_float($score))
                $score = 1;

            //$url = $data['canonical_url'];
            //if ($url == '') {
            // no canonical url
            // redirect to default detail page
            //$url = '/detail/index/id/'.$id;
            $url = "#";
            //}

            $results[] = array('id' => $id, 'url' => $url, 'score' => $score, 'title' => $data['text'], 'abstract' => $data['abstract'], 'author' => $data['author'], 'type' => $data['contentType'], 'lastUpdateTime' => $data['lastUpdateTime'], );
        }
		
		$output['baseUrl'] = $this->view->baseUrl();

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
        $output['type'] = $type;
        $output['lang'] = $lang;
        $output['author'] = $author;
        $output['date'] = $date;

        $output['termSearchRoot'] = $output['baseUrl'].'?query=' . $terms;
        $output['typeSearchRoot'] = $output['termSearchRoot'];
        $output['authorSearchRoot'] = $output['termSearchRoot'];
        $output['dateSearchRoot'] = $output['termSearchRoot'];
        $output['orderBySearchRoot'] = $output['termSearchRoot'];
        $output['pageSizeSearchRoot'] = $output['termSearchRoot'];
        $output['searchRoot'] = $output['termSearchRoot'];

        if ($author != '') {
            $output['typeSearchRoot'] .= '&author=' . $author;
            $output['dateSearchRoot'] .= '&author=' . $author;
            $output['orderBySearchRoot'] .= '&author=' . $author;
            $output['pageSizeSearchRoot'] .= '&author=' . $author;
            $output['searchRoot'] .= '&author=' . $author;
        }

        if ($type != '') {
            $output['authorSearchRoot'] .= '&type=' . $type;
            $output['dateSearchRoot'] .= '&type=' . $type;
            $output['orderBySearchRoot'] .= '&type=' . $type;
            $output['pageSizeSearchRoot'] .= '&type=' . $type;
            $output['searchRoot'] .= '&type=' . $type;
        }

        if ($date != '') {
            $output['typeSearchRoot'] .= '&date=' . $date;
            $output['authorSearchRoot'] .= '&date=' . $date;
            $output['orderBySearchRoot'] .= '&date=' . $date;
            $output['pageSizeSearchRoot'] .= '&date=' . $date;
            $output['searchRoot'] .= '&date=' . $date;
        }

        if ($orderBy != '') {
            $output['typeSearchRoot'] .= '&orderby=' . $orderBy;
            $output['dateSearchRoot'] .= '&orderby=' . $orderBy;
            $output['authorSearchRoot'] .= '&orderby=' . $orderBy;
            $output['pageSizeSearchRoot'] .= '&orderby=' . $orderBy;
            $output['searchRoot'] .= '&orderby=' . $orderBy;
        }

        if ($pageSize != '') {
            $output['typeSearchRoot'] .= '&pagesize=' . $pageSize;
            $output['dateSearchRoot'] .= '&pagesize=' . $pageSize;
            $output['authorSearchRoot'] .= '&pagesize=' . $pageSize;
            $output['orderBySearchRoot'] .= '&pagesize=' . $pageSize;
            $output['searchRoot'] .= '&pagesize=' . $pageSize;
        }


		//$template =  manager::getService('template')->findTemplateFileFor('carrousel');
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/search.html");

        $css = array('/css/rubedo.css', '/css/bootstrap-responsive.css');
        $js = array("/js/jquery.js", "/js/bootstrap-transition.js", "/js/bootstrap-alert.js", "/js/bootstrap-modal.js", "/js/bootstrap-dropdown.js", "/js/bootstrap-scrollspy.js", "/js/bootstrap-tab.js", "/js/bootstrap-tooltip.js", "/js/bootstrap-popover.js", "/js/bootstrap-button.js", "/js/bootstrap-collapse.js", "/js/bootstrap-carousel.js", "/js/bootstrap-typeahead.js", );

       $this->_sendResponse($output, $template, $css, $js);

    }

}
