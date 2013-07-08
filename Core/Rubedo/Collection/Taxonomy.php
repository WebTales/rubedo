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

use Rubedo\Interfaces\Collection\ITaxonomy, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

/**
 * Service to handle Taxonomy
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Taxonomy extends AbstractLocalizableCollection implements ITaxonomy
{

    protected static $nonLocalizableFields = array("mandatory","workspaces","facetOperator","readOnly","order","expandable","inputAsTree","multiSelect");
    
    protected static $labelField = 'name';
    
    protected $_indexes = array(
        array(
            'keys' => array(
                'name' => 1
            ),
            'options' => array(
                'unique' => true
            )
        )
    );

    /**
     * Only access to content with read access
     *
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init ()
    {
        parent::_init();
        
        if (! self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (! in_array('all', $readWorkspaceArray)) {
                $readWorkspaceArray[] = null;
                $readWorkspaceArray[] = 'all';
                $filter = array(
                    'workspaces' => array(
                        '$in' => $readWorkspaceArray
                    )
                );
                $filter = Filter::factory('In')->setName('workspaces')->setValue($readWorkspaceArray);
                $this->_dataService->addFilter($filter);
            }
        }
    }

    /**
     * a virtual taxonomy which reflects sites & pages trees
     *
     * @var array
     */
    protected $_virtualNavigationVocabulary = array(
        'id' => 'navigation',
        'name' => 'Navigation',
        'multiSelect' => true,
        'readOnly' => true,
        'inputAsTree' => true,
        'createUser' => array(
            'fullName' => 'Rubedo'
        ),
        'createTime' => 1363374000,
        'lastUpdateUser' => array(
            'fullName' => 'Rubedo'
        ),
        'lastUpdateTime' => 1363374000
    );

    public function __construct ()
    {
        $this->_collectionName = 'Taxonomy';
        parent::__construct();
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList (\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null)
    {
        $list = parent::getList($filters, $sort, $start, $limit);
        
        $list['data'] = array_merge(array(
            $this->_virtualNavigationVocabulary
        ), $list['data']);
        $list['count'] = $list['count'] + 1;
        
        return $list;
    }

    /**
     * add readOnly information on object
     *
     * @param array $obj            
     * @return array boolean
     */
    protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
            // Set the workspace for old items in database
            if (! isset($obj['workspaces']) || $obj['workspaces'] == "") {
                $obj['workspaces'] = array(
                    'global'
                );
            }
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if (count(array_intersect($obj['workspaces'], $writeWorkspaces)) == 0 || ! Manager::getService('Acl')->hasAccess("write.ui.taxonomy")) {
                $obj['readOnly'] = true;
            }
        }
        
        return $obj;
    }

    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name            
     * @return array
     */
    public function findByName ($name)
    {
        if ($name == 'Navigation') {
            return $this->_virtualNavigationVocabulary;
        }
        $data = $this->_dataService->findOne(Filter::factory('Value')->setName('name')
            ->setValue($name));
        
        if ($data) {
            $data = $this->_addReadableProperty($data);
        }
        return $data;
    }

    /**
     * Allow to find taxonomies associated to the content type id
     *
     * @param string $contentTypeId
     *            Id of the content type
     * @return array Array of results
     */
    public function findByContentTypeID ($contentTypeId)
    {
        $taxonomies = array();
        $contentTypeService = Manager::getService("ContentTypes");
        
        $contentType = $contentTypeService->findById($contentTypeId);
        $taxonomiesId = $contentType["vocabularies"];
        
        foreach ($taxonomiesId as $taxonomyId) {
            $taxonomy = $this->findById($taxonomyId);
            $taxonomyTerms = array();
            $taxonomyTermsObj = Manager::getService("TaxonomyTerms")->findByVocabulary($taxonomyId);
            
            foreach ($taxonomyTermsObj["data"] as $term) {
                $taxonomyTerms[$term["id"]] = $term["text"];
            }
            
            if ($taxonomy["name"] != "Navigation") {
                $taxonomies[$taxonomy["name"]] = array(
                    "id" => $taxonomyId,
                    "terms" => $taxonomyTerms
                );
            }
        }
        
        return $taxonomies;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array())
    {
        $origObj = $this->findById($obj['id']);
        if (! self::isUserFilterDisabled()) {
            if ((isset($origObj['readOnly'])) && ($origObj['readOnly'])) {
                throw new \Rubedo\Exceptions\Access('no rights to update this content', "Exception33");
            }
        }
        
        if ($obj['id'] == 'navigation') {
            throw new \Rubedo\Exceptions\Access('You can not destroy navigation vocabulary', "Exception51");
        }
        $childrenToDelete = Manager::getService('TaxonomyTerms')->findByVocabulary($obj["id"]);
        $deletedTerms = array();
        foreach ($childrenToDelete["data"] as $child) {
            $deletedTerms[] = Manager::getService('TaxonomyTerms')->destroy($child);
        }
        if (! in_array(array(
            "success" => false
        ), $deletedTerms)) {
            return parent::destroy($obj, $options);
        } else {
            return array(
                "success" => false,
                "msg" => "Error during children removal"
            );
        }
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::count()
     */
    public function count (\WebTales\MongoFilters\IFilter $filters = null)
    {
        return parent::count($filters) + 2;
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array())
    {
        if ($obj['name'] == 'Navigation') {
            throw new \Rubedo\Exceptions\Access('You can not create a navigation vocabulary', "Exception52");
        }
        
        $obj = $this->_addDefaultWorkspace($obj);
        return parent::create($obj, $options);
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::findById()
     */
    public function findById ($contentId)
    {
    	if($contentId === null){
    		return null;
    	}
        if ($contentId == 'navigation') {
            return $this->_virtualNavigationVocabulary;
        } else {
            $data = parent::findById($contentId);
            return $data;
        }
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array())
    {
        $origObj = $this->findById($obj['id']);
        if (! self::isUserFilterDisabled()) {
            if ((isset($origObj['readOnly'])) && ($origObj['readOnly'])) {
                throw new \Rubedo\Exceptions\Access('no rights to update this content', "Exception33");
            }
        }
        
        if ($obj['id'] == 'navigation') {
            throw new \Rubedo\Exceptions\Access('You can not update navigation vocabulary', "Exception53");
        }
        if ($obj['name'] == 'Navigation') {
            throw new \Rubedo\Exceptions\Access('can\'t create a navigation vocabulary', "Exception52");
        }
        $obj = $this->_addDefaultWorkspace($obj);
        return parent::update($obj, $options);
    }

    protected function _addDefaultWorkspace ($obj)
    {
        if (! isset($obj['workspaces']) || $obj['workspaces'] == '' || $obj['workspaces'] == array()) {
            $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
            $obj['workspaces'] = array(
                $mainWorkspace['id']
            );
        }
        return $obj;
    }
}
