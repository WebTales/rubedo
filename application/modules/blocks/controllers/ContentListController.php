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
class Blocks_ContentListController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $this->_dateService = Manager::getService('Date');
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');
        $this->_taxonomyReader = Manager::getService('TaxonomyTerms');
        $this->_queryReader = Manager::getService('Queries');
        $blockConfig = $this->getRequest()->getParam('block-config');
        $queryId = $blockConfig['query'];
        $queryConfig = $this->getQuery($queryId);
		$queryType=$queryConfig['type'];
        $contentArray = $this->getContentList($this->setFilters($queryConfig), $this->setPaginationValues($blockConfig));
        $nbItems = $contentArray["count"];
        if ($nbItems > 0) {
            $contentArray['page']['nbPages'] = (int) ceil(($nbItems) / $contentArray['page']['limit']);
            $contentArray['page']['limitPage'] = min(array(
                $contentArray['page']['nbPages'],
                10
            ));
            $typeArray = $this->_typeReader->getList();
            $contentTypeArray = array();
            foreach ($typeArray['data'] as $dataType) {
                /*
                 * $dataType['type']= htmlentities($dataType['type'],
                 * ENT_NOQUOTES, 'utf-8');//Convert special chars to
                 * htmlentities $dataType['type']=
                 * preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#',
                 * '\1', $dataType['type']);//Replace all special char by normal
                 * char $dataType['type']=
                 * preg_replace('#&([A-za-z]{2})(?:lig);#', '\1',
                 * $dataType['type']); // to special char e.g. '&oelig;'
                 */
                
                $path = Manager::getService('FrontOfficeTemplates')->getFileThemePath("/blocks/shortsingle/" . preg_replace('#[^a-zA-Z]#', '', $dataType['type']) . ".html.twig");
                // $contentTypeArray[(string) $dataType['id']]
                if (Manager::getService('FrontOfficeTemplates')->templateFileExists($path)) {
                    $contentTypeArray[(string) $dataType['id']] = $path;
                } else {
                    $contentTypeArray[(string) $dataType['id']] = Manager::getService('FrontOfficeTemplates')->getFileThemePath("/blocks/shortsingle/Default.html.twig");
                }
            }
            foreach ($contentArray['data'] as $vignette) {
                $fields = $vignette['fields'];
                $fields['title'] = $fields['text'];
                unset($fields['text']);
                $fields['id'] = (string) $vignette['id'];
				$fields['typeId']=$vignette['typeId'];
                $fields['type'] = $contentTypeArray[(string) $vignette['typeId']];
                $data[] = $fields;
            }
            $output["data"] = $data;
			$output["query"]['type']=$queryType;
			$output["query"]['id']=$queryId;
            $output['prefix'] = $this->getRequest()->getParam('prefix');
            $output["page"] = $contentArray['page'];
            $output['test'] = array(
                1,
                2,
                3
            );
			
        }
        if (isset($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/contentlist.html.twig");
        }
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
    /*
     * @ todo: PHPDoc
     */
    protected function getContentList ($filters, $pageData)
    {
        $contentArray = $this->_dataReader->getOnlineList($filters["filter"], $filters["sort"], (($pageData['currentPage'] - 1) * $pageData['limit']), $pageData['limit']);
        $contentArray['page'] = $pageData;
        return $contentArray;
    }

	protected function setFilters($query)
	{
		
		if ($query === null) {
            return array();
        }
        $sort = array();
        $operatorsArray = array(
            '$lt' => '<',
            '$lte' => '<=',
            '$gt' => '>',
            '$gte' => '>=',
            '$ne' => '!=',
            'eq' => '='
        );
        if (isset($query['query'])&& $query['type']!="manual") {
            $query = $query['query'];
            /* Add filters on TypeId and publication */
            $filterArray[] = array(
                'operator' => '$in',
                'property' => 'typeId',
                'value' => $query['contentTypes']
            );
            $filterArray[] = array(
                'property' => 'status',
                'value' => 'published'
            );
            
            /* Add filter on taxonomy */
            foreach ($query['vocabularies'] as $key => $value) {
                if (isset($value['rule'])) {
                    if ($value['rule'] == "some") {
                        $taxOperator = '$in';
                    } elseif ($value['rule'] == "all") {
                        $taxOperator = '$all';
                    } elseif ($value['rule'] == "someRec") {
                        if (count($value['terms']) > 0) {
                            foreach ($value['terms'] as $child) {
                                $terms = $this->_taxonomyReader->fetchAllChildren($child);
                                foreach ($terms as $taxonomyTerms) {
                                    $value['terms'][] = $taxonomyTerms["id"];
                                }
                            }
                        }
                        $taxOperator = '$in';
                    }else{
                    	$taxOperator = '$in';
                    }
                } else {
                   $taxOperator = '$in';
                }
                if (count($value['terms']) > 0) {
                    $filterArray[] = array(
                        'operator' => $taxOperator,
                        'property' => 'taxonomy.' . $key,
                        'value' => $value['terms']
                    );
                }
            }
            /* Add filter on FieldRule */
            foreach ($query['fieldRules'] as $property => $value) {
                if (isset($value['rule']) && isset($value['value'])) {
                    $ruleOperator = array_search($value['rule'], $operatorsArray);
                    $nextDate = new DateTime($value['value']);
                    $nextDate->add(new DateInterval('PT23H59M59S'));
                    $nextDate = (array) $nextDate;
                    if ($ruleOperator === 'eq') {
                        $filterArray[] = array(
                            'operator' => '$gt',
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($value['value'])
                        );
                        $filterArray[] = array(
                            'operator' => '$lt',
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($nextDate['date'])
                        );
                    } elseif ($ruleOperator === '$gt') {
                        $filterArray[] = array(
                            'operator' => $ruleOperator,
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($nextDate['date'])
                        );
                    } elseif ($ruleOperator === '$lte') {
                        $filterArray[] = array(
                            'operator' => $ruleOperator,
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($nextDate['date'])
                        );
                    } else {
                        $filterArray[] = array(
                            'operator' => $ruleOperator,
                            'property' => $property,
                            'value' => $this->_dateService->convertToTimeStamp($value['value'])
                        );
                    }
                }
                /*
                 * Add Sort
                 */
                if (isset($value['sort'])) {
                    $sort[] = array(
                        'property' => $property,
                        'direction' => $value['sort']
                    );
                } else {
                    $sort[] = array(
                        'property' => 'id',
                        'direction' => 'DESC'
                    );
                }
            }
        }else{
        	 $filterArray[]=array(
        	 	'operator'=> '$in',
	        	 'property'=> 'id',
	        	 'value'=>$query['query']
			 );
			  $filterArray[]=array(
                'property' => 'status',
                'value' => 'published'
            );
			$sort[] = array(
                        'property' => 'id',
                        'direction' => 'DESC'
                    );
        }
		$returnArray=array("filter"=>$filterArray,"sort"=>$sort);
		return $returnArray;
	}

    protected function setPaginationValues ($blockConfig)
    {
        $pageData['limit'] = isset($blockConfig['pageSize']) ? $blockConfig['pageSize'] : 6;
        $pageData['currentPage'] = $this->getRequest()->getParam("page", 1);
        return $pageData;
    }

    protected function getQuery ($queryId)
    {
    	$this->_queryReader = Manager::getService('Queries');
        return $this->_queryReader->findById($queryId);
    }
	
	public function getContentsAction()
	{
		$this->_dataReader=Manager::getService('Contents');
		$data=$this->getRequest()->getParams();
		$query=$this->getQuery($data['query']);
		$filters=$this->setFilters($query);
		$contentList=$this->_dataReader->getOnlineList($filters['filter'],$filters["sort"]);
		foreach($contentList['data'] as $content)
		{
			$returnArray[]=array('content'=>$content['text'],'id'=>$content['id']);
		}
		 $this->_sendResponse(Zend_Json::encode($returnArray),'contents');
	}
}
