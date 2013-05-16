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

use Rubedo\Interfaces\Collection\IContentTypes,Rubedo\Services\Manager, \WebTales\MongoFilters\Filter;

/**
 * Service to handle ContentTypes
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ContentTypes extends AbstractCollection implements IContentTypes
{
    
    protected $_indexes = array(
        array('keys'=>array('type'=>1),'options'=>array('unique'=>true)),
        //array('keys'=>array('CTType'=>1),'options'=>array('unique'=>true)),
    );
    
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
		    $readWorkspaceArray[] = 'all';
// 		    $filter = array('workspaces'=> array('$in'=>$readWorkspaceArray));
		    $filter = Filter::Factory('OperatorToValue')->setName('workspaces')->setOperator('$in')->setValue($readWorkspaceArray);
		    $this->_dataService->addFilter($filter);
		}
    }

    protected $_model = array(
        'type' => array(
            'domain' => 'name',
            'required' => true
        ),
        'dependant' => array(
            'domain' => 'bool',
            'required' => true
        ),
        'dependantTypes' => array(
            'domain' => 'list',
            'required' => true,
            'items' => array(
                'domain' => 'id',
                'required' => false
            )
        ),
        'fields' => array(
            'domain' => 'list',
            'required' => true,
            'items' => array(
                'domain' => 'array',
                'required' => false,
                'items' => array(
                	'domain' => 'array',
                	'required' => false,
                    'cType' => array(
                        'domain' => 'string',
                        'required' => true
                    ),
                    'config' => array(
                        "name" => array(
                            'domain' => 'string',
                            'required' => true
                        ),
                        "fieldLabel" => array(
                            'domain' => 'string',
                            'required' => true
                        ),
                        "allowBlank" => array(
                            'domain' => 'bool',
                            'required' => false
                        ),
                        "localizable" => array(
                            'domain' => 'bool',
                            'required' => false
                        ),
                        "searchable" => array(
                            'domain' => 'bool',
                            'required' => false
                        ),
                        "multivalued" => array(
                            'domain' => 'bool',
                            'required' => false
                        ),
                        "tooltip" => array(
                            'domain' => 'string',
                            'required' => false
                        ),
                        "labelSeparator" => array(
                            'domain' => 'string',
                            'required' => false
                        )
                    )
                )
            )
        ),
        'vocabularies' => array(
            'domain' => 'list',
            'required' => true,
            'items' => array(
                'domain' => 'id',
                'required' => false
            )
        )
    );

    public function __construct ()
    {
        $this->_collectionName = 'ContentTypes';
        parent::__construct();
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array(), $live = true)
    {    
        if(!isset($obj['workspaces']) || $obj['workspaces']=='' || $obj['workspaces']==array()){
	        $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
	        $obj['workspaces'] = array($mainWorkspace['id']);
	    }
        $returnArray = parent::create($obj, $options, $live);
        
        if ($returnArray["success"]) {
            $this->indexContentType($returnArray['data']);
        }
        return $returnArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array(), $live = true)
    {
        if(!isset($obj['workspaces']) || $obj['workspaces']=='' || $obj['workspaces']==array()){
            $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
            $obj['workspaces'] = array($mainWorkspace['id']);
        }
        $returnArray = parent::update($obj, $options, $live);
        
        if ($returnArray["success"]) {
            $this->indexContentType($returnArray['data']);
        }
        
        return $returnArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array())
    {
        $returnArray = parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->unIndexContentType($obj);
        }
        return $returnArray;
    }

    /**
     * Push the content type to Elastic Search
     *
     * @param array $obj            
     */
    public function indexContentType ($obj)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexContentType($obj['id'], $obj, TRUE);
        
        $ElasticDataIndexService->indexByType('content',$obj['id']);
        
        AbstractCollection::disableUserFilter($wasFiltered);
    }

    /**
     * Remove the content type from Indexed Search
     *
     * @param array $obj            
     */
    public function unIndexContentType ($obj)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteContentType($obj['id'], TRUE);
        
        AbstractCollection::disableUserFilter($wasFiltered);
    }

    /**
     * Find an item given by its name (find only one if many)
     *
     * @param string $name            
     * @return array
     */
    public function findByName ($name)
    {
        return $this->_dataService->findOne(array(
            'type' => $name
        ));
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

	        if (!Manager::getService('Acl')->hasAccess("write.ui.contentTypes") || (count(array_intersect($obj['workspaces'], $writeWorkspaces))==0 && !in_array("all", $writeWorkspaces))) {
	            $obj['readOnly'] = true;
	        }else{
	            $obj['readOnly'] = false;
	        }
		}
        
        return $obj;
    }
	
    /**
     * (non-PHPdoc)
     * @see \Rubedo\Interfaces\Collection\IContentTypes::getReadableContentTypes()
     */
	public function getReadableContentTypes() {
		$currentUserService = Manager::getService('CurrentUser');
		$contentTypesList = array();
		
		$readWorkspaces = $currentUserService->getReadWorkspaces();
		$readWorkspaces[] = NULL;

		if(in_array("all", $readWorkspaces)){
			$filter = array();
		} else {
			$filter = array(array('property' => 'workspaces', 'operator' => '$in', 'value' => $readWorkspaces));
		}
		$filter[] = array('property' => 'system', 'operator' => '$ne', 'value' => true);
		$readableContentTypes = $this->getList($filter);
		
		foreach ($readableContentTypes['data'] as $value) {
			$contentTypesList[$value['type']] = array('type' => $value['type'], 'id' => $value['id']);
		}
		ksort($contentTypesList);
		$contentTypesList = array_values($contentTypesList);
		
		return $contentTypesList;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Rubedo\Interfaces\Collection\IContentTypes::getGeolocatedContentTypes()
	 */
	public function getGeolocatedContentTypes() {
		
		$contentTypesList = $this->getList();
		$geolocatedContentTypes = array();

		foreach ($contentTypesList['data'] as $contentType) {

			$fields=$contentType["fields"];
			foreach($fields as $field) {
				if ($field['config']['name']=='position') {
					$geolocatedContentTypes[] = $contentType['id'];
				}
			}			
		}
		return $geolocatedContentTypes;
	}
}
