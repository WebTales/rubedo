<?php

require_once '/elastica/bootstrap.php';

class ResultController extends AbstractController
{

	public $page;
	public $template;
	public $blocks = array();
	
    public function init()
    {

		$this->blocks = array(
			array('Module'=>'NavBar','Input'=>null,'Output'=>'navbar_content'),
			array('Module'=>'BreadCrumb','Input'=>null,'Output'=>'liens'),
			array('Module'=>'PopIn','Input'=>1,'Output'=>'popin_about'),
			array('Module'=>'PopIn','Input'=>2,'Output'=>'popin_connect'),
			array('Module'=>'PopIn','Input'=>3,'Output'=>'popin_confirm')
		);
		
		$twigVar = array();
		foreach($this->blocks as $block) {
			$helper= 'helper'.$block['Module'];
			$output = $block['Output'];
			$input = $block['Input'];
			$twigVar[$output] = $this->_helper->$helper($input);
		}

		// get terms
		$terms = $this->_getParam('search');
		
		// get type filter
		$type = $this->_getParam('type');
		
		// get lang filter
		$defaultNamespace = new Zend_Session_Namespace('Default');
		$lang = $defaultNamespace->lang;

		// get author filter
		$author = $this->_getParam('author');
		
		// get date filter
		$date = $this->_getParam('date');		
		
		// get pager
		$pager = $this->_getParam('pager');
		if ($pager == '') $pager = 0;
		
		// get orderBy
		$orderBy = $this->_getParam('orderby');
		if ($orderBy == '') $orderBy = "_score";
		
		// get page size
		$pageSize = $this->_getParam('pagesize');
		if ($pageSize == '') $pageSize = 10;
				
		try{
			// New Elastica instance
			$elasticaClient = new Elastica_Client();
	
			// Load index content
			$contentIndex = $elasticaClient->getIndex('content');	
			if (!$contentIndex->exists()) $index->create(array('number_of_shards' => 1,'number_of_replicas' => 0));
			
			// Build global filter
			
			$globalFilter = new Elastica_Filter_And();
						
			// filter on lang
			if ($lang != '') {
				$langFilter = new Elastica_Filter_Term();
        		$langFilter->setTerm('lang', $lang);
				$globalFilter->addFilter($langFilter);
        	}
			
			// filter on type
			if ($type != '') {
				$typeFilter = new Elastica_Filter_Term();
        		$typeFilter->setTerm('type', $type);
				$globalFilter->addFilter($typeFilter);
			}
			
			// filter on author
			if ($author != '') {
				$authorFilter = new Elastica_Filter_Term();
        		$authorFilter->setTerm('author', $author);
				$globalFilter->addFilter($authorFilter);
			}
			
			// filter on date
			if ($date!= '') {
				$dateFilter = new Elastica_Filter_Range();
				$d = $date/1000;
				$dateFrom = $dateTo = mktime(0, 0, 0, date('m',$d), date('d',$d), date('Y',$d))*1000;  
				$dateTo = mktime(0, 0, 0, date('m',$d)+1, date('d',$d), date('Y',$d))*1000;  
        		$dateFilter->addField('dpub', array('from' => $dateFrom, "to" => $dateTo));
				$globalFilter->addFilter($dateFilter);
			}			
			
			// Set query on terms
			$elasticaQueryString = new Elastica_Query_QueryString($terms."*");
			
			$elasticaQuery = new Elastica_Query();
			
			$elasticaQuery->setQuery($elasticaQueryString);
			
			// Apply filters if needed
			$elasticaQuery->setFilter($globalFilter);
						
			// Define the type facet.
			$elasticaFacetType = new Elastica_Facet_Terms('typeFacet');
			$elasticaFacetType->setField('type');
			$elasticaFacetType->setSize(10);
			$elasticaFacetType->setOrder('reverse_count');
			$elasticaFacetType->setFilter($globalFilter);
						
			// Add type facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetType);
			
			// Define the author facet.
			$elasticaFacetAuthor = new Elastica_Facet_Terms('authorFacet');
			$elasticaFacetAuthor->setField('author');
			$elasticaFacetAuthor->setSize(5);
			$elasticaFacetAuthor->setOrder('reverse_count');
			$elasticaFacetAuthor->setFilter($globalFilter);
						
			// Add that facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetAuthor);

			// Define the date facet.
			$elasticaFacetDate = new Elastica_Facet_DateHistogram('dateFacet');
			$elasticaFacetDate->setField('dpub');
			$elasticaFacetDate->setInterval('month');
			$elasticaFacetDate->setFilter($globalFilter);
												
			// Add that facet to the search query object.
			$elasticaQuery->addFacet($elasticaFacetDate);
			
			// Add pagination 		
			if ($pageSize!="all") {
				$elasticaQuery->setSize($pageSize)->setFrom($pager*$pageSize);
			} else {
				
			}
			
			// add sort
			$elasticaQuery->setSort(array($orderBy =>"desc"));
			
			// run query
			$elasticaResultSet = $contentIndex->search($elasticaQuery);
			
			// Get total hits
			$nbResults 	= $elasticaResultSet->getTotalHits();
			if ($pageSize!="all") {
				$pageCount = intval($nbResults / $pageSize)+1;
			} else {
				$pageCount = 1;
			}
			
			// Get facets from the result of the search query
			$elasticaFacets = $elasticaResultSet->getFacets();
		
			$elasticaResults = $elasticaResultSet->getResults();
			
		} catch (Exception $e) {
            var_dump($e->getMessage());
			exit;
        }
		
		$results = array();
		
		foreach($elasticaResults as $result) {

			$data = $result->getData();
			$resultType = $result->getType();
			$lang_id = explode('_',$result->getId());
			$id = $lang_id[1];
			
			$score = $result->getScore();
			
			if (!is_float($score)) $score = 1;
			
			$url = $data['canonical_url'];
			if ($url == '') {
				// no canonical url
				// redirect to default detail page
				$url = '/detail/index/id/'.$id;
			}
			
			$results[] = array(
				'id' => $id,
				'url' => $url,
				'score' => $score,
				'title' => $data['title'],
				'description' => $data['description'],
				'author' => $data['author'],
				'type' => $resultType, 
				'dpub' => $data['dpub'],
				);
		}
		
		$twigVar['searchTerms'] = $terms;
		$twigVar['results'] = $results;
		$twigVar['nbResults'] = $nbResults;
		$twigVar['pager'] = $pager;
		$twigVar['pageCount'] = $pageCount;
		$twigVar['pageSize'] = $pageSize;
		$twigVar['orderBy'] = $orderBy;
		
		$twigVar['typeFacets'] = $elasticaFacets['typeFacet']['terms'];
		$twigVar['authorFacets'] = $elasticaFacets['authorFacet']['terms'];
		$twigVar['dateFacets'] = $elasticaFacets['dateFacet']['entries'];
		
		$twigVar['type'] = $type;
		$twigVar['lang'] = $lang;
		$twigVar['author'] = $author;
		$twigVar['date'] = $date;
		
		$twigVar['termSearchRoot'] = '/result/index/search/'.$terms;
		$twigVar['typeSearchRoot'] = $twigVar['termSearchRoot'];
		$twigVar['authorSearchRoot'] = $twigVar['termSearchRoot'];
		$twigVar['dateSearchRoot'] = $twigVar['termSearchRoot'];
		$twigVar['orderBySearchRoot'] = $twigVar['termSearchRoot'];
		$twigVar['pageSizeSearchRoot'] = $twigVar['termSearchRoot'];
		$twigVar['searchRoot'] = $twigVar['termSearchRoot'];

		if ($author != '') {
			$twigVar['typeSearchRoot'].='/author/'.$author;
			$twigVar['dateSearchRoot'].='/author/'.$author;
			$twigVar['orderBySearchRoot'].='/author/'.$author;
			$twigVar['pageSizeSearchRoot'].='/author/'.$author;
			$twigVar['searchRoot'].='/author/'.$author;
		}

		if ($type != '') {
			$twigVar['authorSearchRoot'].='/type/'.$type;
			$twigVar['dateSearchRoot'].='/type/'.$type;
			$twigVar['orderBySearchRoot'].='/type/'.$type;
			$twigVar['pageSizeSearchRoot'].='/type/'.$type;
			$twigVar['searchRoot'].='/type/'.$type;
		}		

		if ($date != '') {
			$twigVar['typeSearchRoot'].='/date/'.$date;
			$twigVar['authorSearchRoot'].='/date/'.$date;
			$twigVar['orderBySearchRoot'].='/date/'.$date;
			$twigVar['pageSizeSearchRoot'].='/date/'.$date;
			$twigVar['searchRoot'].='/date/'.$date;
		}	
		
		if ($orderBy != '') {
			$twigVar['typeSearchRoot'].='/orderby/'.$orderBy;
			$twigVar['dateSearchRoot'].='/orderby/'.$orderBy;
			$twigVar['authorSearchRoot'].='/orderby/'.$orderBy;
			$twigVar['pageSizeSearchRoot'].='/orderby/'.$orderBy;
			$twigVar['searchRoot'].='/orderby/'.$orderBy;
		}	

		if ($pageSize != '') {
			$twigVar['typeSearchRoot'].='/pagesize/'.$pageSize;
			$twigVar['dateSearchRoot'].='/pagesize/'.$pageSize;
			$twigVar['authorSearchRoot'].='/pagesize/'.$pageSize;
			$twigVar['orderBySearchRoot'].='/pagesize/'.$pageSize;
			$twigVar['searchRoot'].='/pagesize/'.$pageSize;
		}	
						
		
        $this->twig('result.html',$twigVar);

    }
		
    public function indexAction()
    {

    }
	
}