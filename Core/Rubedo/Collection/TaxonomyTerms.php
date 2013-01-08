<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
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
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array('safe'=>true,))
    {
        if ($obj['vocabularyId'] == 'navigation') {
            throw new \Exception('can\'t create navigation terms ');
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
            if ($currenPage) {
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
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $navigation = false;
        
        if (is_array($filters)) {
            foreach ($filters as $key => $filter) {
                if (($filter['property'] == 'vocabularyId' && $filter['value'] == 'navigation')) {
                    $navigation = true;
                    unset($filters[$key]);
                }
            }
        }
        
        if (! $navigation) {
            return parent::getList($filters, $sort, $start, $limit);
        } else {
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
        }
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::readChild()
     */
    public function readChild ($parentId, $filters = null, $sort = null)
    {
        $navigation = false;
        
        if (is_array($filters)) {
            foreach ($filters as $key => $filter) {
                if (($filter['property'] == 'vocabularyId' && $filter['value'] == 'navigation')) {
                    $navigation = true;
                    unset($filters[$key]);
                }
            }
        }
        
        if (! $navigation) {
            return parent::readChild($parentId, $filters, $sort);
        } else {
            if ($parentId == 'root') {
                $returnArray = array();
                $childrenArray = Manager::getService('Sites')->getList($filters, $sort);
                foreach ($childrenArray['data'] as $site) {
                    $returnArray[] = $this->_siteToTerm($site);
                }
                
                return array_values($returnArray);
            } else {
                $rootPage = Manager::getService('Pages')->findById($parentId);
                if ($rootPage) {
                    $filter[] = array(
                        'property' => 'site',
                        'value' => $rootPage["site"]
                    );
                } else {
                    $filter[] = array(
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
        }
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
        $term["parentId"] = 'root';
        $term['text'] = $site['text'];
        $term['id'] = $site['id'];
        $term['vocabularyId'] = 'navigation';
        return $term;
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
        $term['leaf'] = $page['leaf'];
        $term['orderValue'] = $page['orderValue'];
        $term['vocabularyId'] = 'navigation';
        
        return $term;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true,))
    {
        if (isset($obj['vocabularyId']) && ($obj['vocabularyId'] == 'navigation')) {
            throw new \Exception('can\'t alter navigation terms ');
        }
        parent::update($obj, $options);
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
    public function destroy (array $obj, $options = array('safe'=>true))
    {
        if (isset($obj['vocabularyId']) && ($obj['vocabularyId'] == 'navigation')) {
            throw new \Exception('can\'t destroy navigation terms ');
        }
        $deleteCond = array(
            '_id' => array(
                '$in' => $this->_getChildToDelete($obj['id'])
            )
        );
        
        $resultArray = $this->_dataService->customDelete($deleteCond);
        
        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] > 0) {
                $returnArray = array(
                    'success' => true
                );
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'no record had been deleted'
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
            self::$_termsArray[$id] = $term['text'];
        }
        return self::$_termsArray[$id];
    }

    /**
     * Clear orphan terms in the collection
     *
     * @return array Result of the request
     */
    public function clearOrphanTerms ()
    {
        $taxonomy = \Rubedo\Services\Manager::getService('Taxonomy');
        $orphans = array();
        $erreur = false;
        
        $terms = $this->getList();
        $terms = $terms['data'];
        
        foreach ($terms as $value) {
            if (isset($value['vocabularyId'])) {
                $vocabulary = $taxonomy->findById($value['vocabularyId']);
                
                if (! $vocabulary) {
                    $orphans[] = $value;
                } else {
                    if (isset($value['parentId'])) {
                        if (! $value['parentId'] == "root") {
                            $parent = $this->findById($value['parentId']);
                            
                            if (! $parent) {
                                $orphans[] = $value;
                            }
                        }
                    } else {
                        $orphans[] = $value;
                    }
                }
            } else {
                $orphans[] = $value;
            }
        }
        
        foreach ($orphans as $value) {
            $result = $this->destroy($value);
            
            if (! $result['success']) {
                $erreur = true;
            }
        }
        
        if (! $erreur) {
            $response['success'] = true;
            $response['data'] = $orphans;
        } else {
            $response['success'] = false;
        }
        
        return $response;
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
        $filter = array(
            "property" => "vocabularyId",
            "value" => $vocabularyId
        );
        return $this->getList($filter);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\ITaxonomyTerms::deleteByVocabularyId()
     */
    public function deleteByVocabularyId ($id)
    {
        if (isset($obj['vocabularyId']) && ($obj['vocabularyId'] == 'navigation')) {
            throw new \Exception('can\'t destroy navigation terms ');
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
        throw new \Exception('name is not unique');
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
}
