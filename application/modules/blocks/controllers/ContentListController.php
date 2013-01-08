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
		$this->_queryReader=Manager::getService('Queries');
        $queryId = $this->getRequest()->getParam('block-config');
		$blockConfig= $this->_queryReader->findById($queryId["query"]);

        $contentArray = $this->getDataList($blockConfig, $this->setPaginationValues($blockConfig));

        $nbItems = $contentArray["count"];
        if ($nbItems > 0) {
            $contentArray['page']['nbPages'] = (int) ceil(($nbItems) / $contentArray['page']['limit']);
            $contentArray['page']['limitPage'] = min(array(
                $contentArray['page']['nbPages'],
                3
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
                $contentTypeArray[(string) $dataType['id']] = Manager::getService('FrontOfficeTemplates')->getFileThemePath("/blocks/shortsingle/" . preg_replace('#[^a-zA-Z]#', '', $dataType['type']) . ".html.twig");
            }
            foreach ($contentArray['data'] as $vignette) {
                $fields = $vignette['fields'];
                $fields['title'] = $fields['text'];
                unset($fields['text']);
                $fields['id'] = (string) $vignette['id'];
                $fields['type'] = $contentTypeArray[(string) $vignette['typeId']];
                $data[] = $fields;
            }
            $output["data"] = $data;
            $output['prefix'] = $this->getRequest()->getParam('prefix');
            $output["page"] = $contentArray['page'];
            $output['test']=array(1,2,3);
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

    protected function getDataList ($blockConfig, $pageData)
    {
    	$sort = array();
        $operatorsArray = array(
            '$lt' => '<',
            '$lte' => '<=',
            '$gt' => '>',
            '$gte' => '>=',
            '$ne' => '!=',
            'eq' => '='
        );
        if (isset($blockConfig['query'])) {
        	$blockConfig=$blockConfig['query'];
            /* Add filters on TypeId and publication */
            $filterArray[] = array(
                'operator' => '$in',
                'property' => 'typeId',
                'value' => $blockConfig['contentTypes']
            );
            $filterArray[] = array(
                'property' => 'status',
                'value' => 'published'
            );
            /* Add filter on taxonomy */
            foreach ($blockConfig['vocabularies'] as $key => $value) {
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
                    }
                }else{
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
            foreach ($blockConfig['fieldRules'] as $property => $value) {
            	if(isset($value['rule'])&& isset($value['value'])){
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
				 if(isset($value['sort'])){
       		$sort[] = array(
            'property' => $property,
            'direction' => $value['sort']
        );	
				 }else{
				 $sort[] = array(
            'property' =>'id',
            'direction' => 'DESC');
				 }
			
            }
        } else {
            // no advanced query : should get classic parameters
            $output = array();
            if (isset($blockConfig['contentTypes'])) {
                $contentTypesArray = $blockConfig['contentTypes'];
                if (is_array($contentTypesArray) && count($contentTypesArray) > 0) {
                    $filterArray[] = array(
                        'operator' => '$in',
                        'property' => 'typeId',
                        'value' => $contentTypesArray
                    );
                }
            }
            
            if (isset($blockConfig['taxonomy'])) {
                $taxonomyTermsArray = $blockConfig['taxonomy'];
                if (is_array($taxonomyTermsArray) && count($taxonomyTermsArray) > 0) {
                    
                    $filterArray[] = array(
                        'operator' => '$in',
                        'property' => 'taxonomy.50c0cabc9a199dcc0f000002', // @todo
                                                                           // :
                                                                           // taxanomy
                                                                           // parameter
                                                                           // do
                                                                           // not
                                                                           // return
                                                                           // vocabulary
                                                                           // !
                        'value' => $taxonomyTermsArray
                    );
                }
            }
            $filterArray[] = array(
                'property' => 'status',
                'value' => 'published'
            );
        }
        /* Get the list */
        $contentArray = $this->_dataReader->getOnlineList($filterArray, $sort, (($pageData['currentPage'] - 1) * $pageData['limit']), $pageData['limit']);
        $contentArray['page'] = $pageData;
        return $contentArray;
    }

    protected function setPaginationValues ($blockConfig)
    {
        $pageData['limit'] = isset($blockConfig['pageSize']) ? $blockConfig['pageSize'] : 6;
        $pageData['currentPage'] = $this->getRequest()->getParam("page", 1);
        return $pageData;
    }
}
