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

use Rubedo\Interfaces\Collection\ITaxonomy, Rubedo\Services\Manager;

/**
 * Service to handle Taxonomy
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Taxonomy extends AbstractCollection implements ITaxonomy
{

    /**
     * Only access to content with read access
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init(){
        parent::_init();
		
		if (! self::isUserFilterDisabled()) {
	        $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
	        if(in_array('all',$readWorkspaceArray)){
	            return;
	        }
	        $readWorkspaceArray[] = null;
	        $filter = array('workspaces'=> array('$in'=>$readWorkspaceArray));
	        $this->_dataService->addFilter($filter);
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
        'readOnly'=>true
    );

    public function __construct ()
    {
        $this->_collectionName = 'Taxonomy';
        parent::__construct();
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $list = parent::getList($filters, $sort, $start, $limit);
		
		foreach ($list['data'] as &$obj){
            $obj = $this->_addReadableProperty($obj);
        }
		
        $list['data'] = array_merge(array(
            $this->_virtualNavigationVocabulary,
        ), $list['data']);
        $list['count'] = $list['count'] + 1;
		
        return $list;
    }
	
	protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
        	//Set the workspace for old items in database		
	        if (! isset($obj['workspaces']) || $obj['workspaces']=="") {
	            $obj['workspaces'] = array(
	                'global'
	            );
	        }
	        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
	        
	        if (count(array_intersect($obj['workspaces'], $writeWorkspaces)) == 0) {
	            $obj['readOnly'] = true;
	        } else {
	            
	            $obj['readOnly'] = false;
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
        return $this->_dataService->findOne(array(
            'name' => $name
        ));
    }

    public function destroy (array $obj, $options = array('safe'=>true))
    {
        if ($obj['id'] == 'navigation') {
            throw new \Rubedo\Exceptions\Access('can\'t destroy navigation');
        }
        $childDelete = Manager::getService('TaxonomyTerms')->deleteByVocabularyId($obj['id']);
        if ($childDelete["ok"] == 1) {
            return parent::destroy($obj, $options);
        } else {
            return $childDelete;
        }
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::count()
     */
    public function count ($filters = null)
    {
        return parent::count($filters) + 2;
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array('safe'=>true,))
    {
        if ($obj['name'] == 'Navigation') {
            throw new \Rubedo\Exceptions\Access('can\'t create a navigation vocabulary');
        }

		$obj = $this->_addDefaultWorkspace($obj);
        return parent::create($obj, $options);
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::findById()
     */
    public function findById ($contentId)
    {
        if ($contentId == 'navigation') {
            return $this->_virtualNavigationVocabulary;
        } else {
            return parent::findById($contentId);
        }
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true,))
    {
        if ($obj['id'] == 'navigation') {
            throw new \Rubedo\Exceptions\Access('can\'t update navigation vocabulary');
        }
        if ($obj['name'] == 'Navigation') {
            throw new \Rubedo\Exceptions\Access('can\'t create a navigation vocabulary');
        }
		$obj = $this->_addDefaultWorkspace($obj);
        return parent::update($obj, $options);
    }
	
	protected function _addDefaultWorkspace($obj){
		if(!isset($obj['workspaces']) || $obj['workspaces']=='' || $obj['workspaces']==array()){
	        $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
	        $obj['workspaces'] = array($mainWorkspace['id']);
	    }
		return $obj;
	}
}
