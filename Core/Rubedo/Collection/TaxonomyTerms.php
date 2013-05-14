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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\ITaxonomyTerms, Rubedo\Services\Manager;

/**
 * Service to handle TaxonomyTerms
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class TaxonomyTerms extends AbstractCollection implements ITaxonomyTerms
{

    protected $_indexes = array(
        array(
            'keys' => array(
                'vocabularyId' => 1,
                "parentId" => 1,
                "orderValue" => 1
            )
        ),
        array(
            'keys' => array(
                'text' => 1,
                'vocabularyId' => 1,
                "parentId" => 1
            ),
            'options' => array(
                'unique' => true
            )
        )
    );

    public function __construct ()
    {
        $this->_collectionName = 'TaxonomyTerms';
        parent::__construct();
    }

    /**
     * Array of already read terms
     *
     * @var array
     */
    protected static $_termsArray = array();

    /**
     * Virtual term for navigation taxonomy : alias for the current Page when doing queries
     * 
     * @var array
     */
    protected $_virtualCurrentPageTerm = array(
        "parentId" => 'root',
        "text" => "Page Courante",
        "id" => "currentPage",
        "expandable" => false,
        "vocabularyId" => 'navigation',
        "canAssign" => 'true',
        "readOnly" => true
    );
    

    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array())
    {
        if ($obj['vocabularyId'] == 'navigation') {
            throw new \Rubedo\Exceptions\Access('can\'t create navigation terms ');
        }
        return parent::create($obj, $options);
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getAncestors()
     */
    public function getAncestors ($item, $limit = 10)
    {
        if (isset($item['vocabularyId']) && $item['vocabularyId'] == 'navigation') {
            if ($item['parentId'] == 'root') {
                return array();
            }
            $currentPage = Manager::getService('Pages')->findById($item['id']);
            if ($currentPage && isset($currentPage['site'])) {
                $site = Manager::getService('Sites')->findById($currentPage['site']);
                $returnArray = array();
                $returnArray[] = $this->_siteToTerm($site);
                $pageAncestors = Manager::getService('Pages')->getAncestors($item, $limit);
                foreach ($pageAncestors as $page) {
                    $returnArray[] = $this->_pageToTerm($page);
                }
                return $returnArray;
            } else {
                return array();
            }
        } else {
            return parent::getAncestors($item, $limit);
        }
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList (\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null)
    {
        $navigation = false;
        
        if($filters){
            $navigation = $this->_lookForNavigation($filters);
        }
        
        if ($navigation) {
            $siteList = Manager::getService('Sites')->getList($filters);
            $contentArray = array();
            foreach ($siteList['data'] as $site) {
                $contentArray[] = $this->_siteToTerm($site);
            }
            $pageList = Manager::getService('Pages')->getList($filters);
            foreach ($pageList['data'] as $page) {
                $contentArray[] = $this->_pageToTerm($page);
            }
            
            $number = count($contentArray);
            return array(
                'count' => $number,
                'data' => $contentArray
            );
        } else {
            return parent::getList($filters, $sort, $start, $limit);
        }
    }
    
    protected function _lookForNavigation(\WebTales\MongoFilters\IFilter $filters){
        if($filters instanceof \WebTales\MongoFilters\ICompositeFilter){ //do recursive adaptation to composite filter
            $filtersArray = $filters->getFilters();
            foreach ($filtersArray as $filter){
               $result = $this->_lookForNavigation($filter) || $result;
            }
        }elseif($filters instanceof \WebTales\MongoFilters\ValueFilter){ // adapt simple filters
            $key = $filters->getName();
            $value = $filters->getValue();
            if($key == 'vocabularyId' && $value == 'navigation'){
                unset($filters);
                $result = true;                
            }
        }
        return $result;
    }
    
    

	/*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::readChild()
     */
    public function readChild ($parentId,\WebTales\MongoFilters\IFilter $filters = null, $sort = null)
    {
        $navigation = false;
        
        if($filters){
            $navigation = $this->_lookForNavigation($filters);
        }
        
        $parentItem = $this->findById($parentId);
        if($parentItem['vocabularyId']=='navigation'){
            $navigation = true;
        }
        if ($navigation) {
            if ($parentId == 'root') {
                $returnArray = array();
                
                $returnArray[] = $this->_getMainRoot();
                
                return array_values($returnArray);
            } elseif ($parentId == 'all') {
                $returnArray = array();
                $childrenArray = Manager::getService('Sites')->getList($filters, $sort);
                foreach ($childrenArray['data'] as $site) {
                    $returnArray[] = $this->_siteToTerm($site);
                }
                
                return array_values($returnArray);
            } else {
                $rootPage = Manager::getService('Pages')->findById($parentId);
                
                if ($rootPage) {
                    $filters[] = array(
                        'property' => 'site',
                        'value' => $rootPage["site"]
                    );
                } else {
                    $filters[] = array(
                        'property' => 'site',
                        'value' => $parentId
                    );
                    $parentId = 'root';
                }
                
                $returnArray = array();
                $childrenArray = Manager::getService('Pages')->readChild($parentId, $filters);
               
                
                foreach ($childrenArray as $page) {
                    $returnArray[] = $this->_pageToTerm($page);
                }
                
                return array_values($returnArray);
            }
            return array();
        } else {
            return parent::readChild($parentId, $filters, $sort);
        }
    }

    public function getNavigationTree ($withCurrentPage = false)
    {
        $mainRoot = $this->_getMainRoot();
        $siteArray = Manager::getService('Sites')->getList();
        $childrenArray = array();
        if($withCurrentPage){
            $childrenArray[]=$this->_virtualCurrentPageTerm;
        }
        foreach ($siteArray['data'] as $site) {
            $childrenArray[] = $this->_siteToTerm($site);
        }
        if (count($childrenArray) > 0) {
            foreach ($childrenArray as $key => $value) {
                $childrenArray[$key] = $this->_addChildrenToSite($value);
            }
        }
        $mainRoot['children'] = $childrenArray;
        return $mainRoot;
    }

    protected function _addChildrenToSite ($array)
    {
        $sort[] = array(
            'property' => 'orderValue',
            'direction' => 'ASC'
        );
        $filters[] = array(
            'property' => 'site',
            'value' => $array['id']
        );
        $children = Manager::getService('Pages')->readChild('root', $filters, $sort);
        if (count($children) > 0) {
            $array['expandable'] = true;
            $array['children'] = array();
            foreach ($children as $child) {
                $child = $this->_pageToTerm($child);
                $array['children'][] = $this->_addNavigationChildrenToArray($child);
            }
        }
        return $array;
    }

    protected function _addNavigationChildrenToArray ($array)
    {
        $filters = null;
        
        $sort[] = array(
            'property' => 'orderValue',
            'direction' => 'ASC'
        );
        $children = Manager::getService('Pages')->readChild($array['id'], $filters, $sort);
        if (count($children) > 0) {
            $array['expandable'] = true;
            $array['children'] = array();
            foreach ($children as $child) {
                $child = $this->_pageToTerm($child);
                $array['children'][] = $this->_addNavigationChildrenToArray($child);
            }
        }
        return $array;
    }

    /**
     * convert a site item to a taxonomy term item
     *
     * @param array $workspace            
     * @return array
     */
    protected function _workspaceToTerm ($workspace)
    {
        $term = array();
        $term["parentId"] = 'root';
        $term['text'] = $workspace['text'];
        $term['id'] = $workspace['id'];
        $term['vocabularyId'] = 'wokspaces';
        $term['isNotPage']=true;
        if (! self::isUserFilterDisabled()) {
            $term['readOnly'] = true;
        }
        $term['leaf'] = true;
        return $term;
    }

    /**
     * convert a site item to a taxonomy term item
     *
     * @param array $site            
     * @return array
     */
    protected function _siteToTerm ($site)
    {
        $term = array();
        $term["parentId"] = 'all';
        $term['text'] = $site['text'];
        $term['id'] = $site['id'];
        $term['vocabularyId'] = 'navigation';
        $term['isNotPage'] = true;
        $term['canAssign'] = (isset($site['readOnly']) && $site['readOnly']) ? false : true;
        if (! self::isUserFilterDisabled()) {
            $term['readOnly'] = true;
        }
        $term['leaf'] = true;
        return $term;
    }

    protected function _getMainRoot ()
    {
        $mainRoot = array();
        $mainRoot["parentId"] = 'root';
        $mainRoot['text'] = 'Tous les sites';
        $mainRoot['id'] = 'all';
        $mainRoot['canAssign'] = true;
        $mainRoot['isNotPage'] = true;
        $mainRoot['vocabularyId'] = 'navigation';
        if (! self::isUserFilterDisabled()) {
            $mainRoot['readOnly'] = true;
        }
        return $mainRoot;
    }

    /**
     * convert a page item to a taxonomy term item
     *
     * @param array $site            
     * @return array
     */
    protected function _pageToTerm ($page)
    {
        $term = array();
        $term["parentId"] = ($page['parentId'] == 'root') ? $page['site'] : $page['parentId'];
        $term['text'] = $page['text'];
        $term['id'] = $page['id'];
        unset($term['leaf']);
        $term['expandable'] = $page['expandable'];
        $term['orderValue'] = $page['orderValue'];
        $term['vocabularyId'] = 'navigation';
        $term['canAssign'] = (isset($page['readOnly']) && $page['readOnly']) ? false : true;
        if (! self::isUserFilterDisabled()) {
            $term['readOnly'] = true;
        }
        
        return $term;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array())
    {
        if (isset($obj['vocabularyId']) && ($obj['vocabularyId'] == 'navigation')) {
            throw new \Rubedo\Exceptions\Access('can\'t alter navigation terms ');
        }
        return parent::update($obj, $options);
    }

    /**
     * Delete objects in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function destroy (array $obj, $options = array())
    {
        if (isset($obj['vocabularyId']) && ($obj['vocabularyId'] == 'navigation')) {
            throw new \Rubedo\Exceptions\Access('can\'t destroy navigation terms ');
        }
        $childrenToDelete = $this->_getChildToDelete($obj['id']);
        
        $deleteCond = array(
            '_id' => array(
                '$in' => $childrenToDelete
            )
        );
        foreach ($childrenToDelete as $child) {
            $updateContent = Manager::getService('Contents')->unsetTerms($obj["vocabularyId"], $child);
        }
        
        $resultArray = $this->_dataService->customDelete($deleteCond);
        
        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] > 0) {
                $returnArray = array(
                    'success' => true
                );
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'La suppression a échoué'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => $resultArray["err"]
            );
        }
        return $returnArray;
    }

    /**
     *
     * @param string $id
     *            id whose children should be deleted
     * @return array array list of items to delete
     */
    protected function _getChildToDelete ($id)
    {
        // delete at least the node
        $returnArray = array(
            $this->_dataService->getId($id)
        );
        
        // read children list
        $terms = $this->readChild($id);
        
        // for each child, get sublist of children
        if (is_array($terms)) {
            foreach ($terms as $key => $value) {
                $returnArray = array_merge($returnArray, $this->_getChildToDelete($value['id']));
            }
        }
        
        return $returnArray;
    }

    /**
     * Allow to find a term by its id
     *
     * @param string $id
     *            id of the term
     * @param string $vocabularyId
     *            id of the vocabulary
     * @return array the term
     */
    public function getTerm ($id, $vocabularyId = null)
    {
        if (! isset(self::$_termsArray[$id])) {
            if ($vocabularyId == null || $vocabularyId != 'navigation') {
                $term = parent::findById($id);
            } else {
                $term = Manager::getService('Sites')->findById($id);
                if (! $term) {
                    $term = Manager::getService('Pages')->findById($id);
                    if ($term) {
                        $term = $this->_pageToTerm($term);
                    }
                } else {
                    $term = $this->_siteToTerm($term);
                }
            }
            if (! isset($term['text'])) {
                return null;
            }
            
            $vocabulary = Manager::getService('Taxonomy')->findById($term["vocabularyId"]);
            
            self::$_termsArray[$id] = array($vocabulary["name"] => $term['text']);
        }
        return self::$_termsArray[$id];
    }

    /**
     * Allow to find terms by their vocabulary
     *
     * @param string $vocabularyId
     *            Contain the id of the vocabulary
     * @return array Contain the terms associated to the vocabulary given in
     *         parameter
     */
    public function findByVocabulary ($vocabularyId)
    {
        $filters = array();
        $filters[] = array(
            "property" => "vocabularyId",
            "value" => $vocabularyId
        );
        return $this->getList($filters);
    }
    
    /**
	 * Allow to find term by vocabularyId and name
	 *
	 * @param string $vocabularyId Contain the id of the vocabulary
	 * @param string $name Contain the name of the term
	 * @return array Contain the terms associated to the vocabulary given in parameter
	 */
    public function findByVocabularyIdAndName ($vocabularyId,$name)
    {
        $cond = array();
        $cond['vocabularyId'] = $vocabularyId;
        $cond['text'] = $name;
        return $this->_dataService->findOne($cond);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\ITaxonomyTerms::deleteByVocabularyId()
     */
    public function deleteByVocabularyId ($id)
    {
        if ($id == 'navigation') {
            throw new \Rubedo\Exceptions\Access('can\'t destroy navigation terms ');
        }
        $deleteCond = array(
            'vocabularyId' => $id
        );
        return $this->_dataService->customDelete($deleteCond);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::findByName()
     */
    public function findByName ($name)
    {
        throw new \Rubedo\Exceptions\Access('name is not unique');
    }

    /**
     * Allow to find a term by its id
     *
     * @param string $id
     *            id of the term
     * @param string $vocabularyId
     *            id of the vocabulary
     * @return array the term
     */
    public function findById ($id)
    {
        $term = parent::findById($id);
        if (! $term) {
            $term = Manager::getService('Sites')->findById($id);
            if (! $term) {
                $term = Manager::getService('Pages')->findById($id);
                if ($term) {
                    $term = $this->_pageToTerm($term);
                }
            } else {
                $term = $this->_siteToTerm($term);
            }
        }
        return $term;
    }

    public function clearOrphanTerms ()
    {
        $taxonomyService = Manager::getService('Taxonomy');
        $taxonomyArray = array();
        $taxonomyIdArray = array();
        $termsArray = array();
        $termsIdArray = array(
            'root'
        );
        $orphansArray = array();
        $orphansIdArray = array();
        
        $taxonomyArray = $taxonomyService->getList();
        $termsArray = $this->getList();
        
        foreach ($taxonomyArray['data'] as $value) {
            $taxonomyIdArray[] = $value['id'];
        }
        
        foreach ($termsArray['data'] as $value) {
            $termsIdArray[] = $value['id'];
        }
        
        $orphansArray = $this->_dataService->customFind(array(
            '$or' => array(
                array(
                    'parentId' => array(
                        '$nin' => $termsIdArray
                    )
                ),
                array(
                    'vocabularyId' => array(
                        '$nin' => $taxonomyIdArray
                    )
                )
            )
        ));
        
        if ($orphansArray->count() > 0) {
            $orphansArray = iterator_to_array($orphansArray);
        } else {
            $orphansArray = array();
        }
        
        foreach ($orphansArray as $value) {
            $orphansIdArray[] = $value['_id'];
        }
        
        $result = $this->_deleteByArrayOfId($orphansIdArray);
        
        if ($result['ok'] == 1) {
            return array(
                'success' => 'true'
            );
        } else {
            return array(
                'success' => 'false'
            );
        }
    }

    public function countOrphanTerms ()
    {
        $taxonomyService = Manager::getService('Taxonomy');
        $taxonomyArray = array();
        $taxonomyIdArray = array();
        $termsArray = array();
        $termsIdArray = array(
            'root'
        );
        $orphansArray = array();
        
        $taxonomyArray = $taxonomyService->getList();
        $termsArray = $this->getList();
        
        foreach ($taxonomyArray['data'] as $value) {
            $taxonomyIdArray[] = $value['id'];
        }
        
        foreach ($termsArray['data'] as $value) {
            $termsIdArray[] = $value['id'];
        }
        
        $orphansArray = $this->_dataService->customFind(array(
            '$or' => array(
                array(
                    'parentId' => array(
                        '$nin' => $termsIdArray
                    )
                ),
                array(
                    'vocabularyId' => array(
                        '$nin' => $taxonomyIdArray
                    )
                )
            )
        ));
        
        if ($orphansArray->count() > 0) {
            $orphansArray = iterator_to_array($orphansArray);
        } else {
            $orphansArray = array();
        }
        
        return count($orphansArray);
    }

    protected function _deleteByArrayOfId ($arrayId)
    {
        $deleteArray = array();
        foreach ($arrayId as $stringId) {
            $deleteArray[] = $this->_dataService->getId($stringId);
        }
        return $this->_dataService->customDelete(array(
            '_id' => array(
                '$in' => $deleteArray
            )
        ));
    }
}
