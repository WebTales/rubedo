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

use Rubedo\Interfaces\Collection\IDamTypes, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

/**
 * Service to handle Groups
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class DamTypes extends AbstractLocalizableCollection implements IDamTypes
{
    protected static $nonLocalizableFields = array("fields","vocabularies","mainFileType","activateDisqus","readOnly","workspaces");
    protected static $labelField = 'type';
    protected $_indexes = array(
        array(
            'keys' => array(
                'type' => 1
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
            if (in_array('all', $readWorkspaceArray)) {
                return;
            }
            $readWorkspaceArray[] = null;
            $readWorkspaceArray[] = 'all';
            $filter = array(
                'workspaces' => array(
                    '$in' => $readWorkspaceArray
                )
            );
            $filter = Filter::factory('OperatorToValue')->setName('workspaces')
                ->setOperator('$in')
                ->setValue($readWorkspaceArray);
            $this->_dataService->addFilter($filter);
        }
    }

    public function __construct ()
    {
        $this->_collectionName = 'DamTypes';
        parent::__construct();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array())
    {
        $obj = $this->_addDefaultWorkspace($obj);
        
        $returnArray = parent::create($obj, $options);
        
        if ($returnArray["success"]) {
            $this->_indexDamType($returnArray['data']);
        }
        
        return $returnArray;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array())
    {
        $obj = $this->_addDefaultWorkspace($obj);
        
        $returnArray = parent::update($obj, $options);
        
        if ($returnArray["success"]) {
            $this->_indexDamType($returnArray['data']);
        }
        
        return $returnArray;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $options = array())
    {
        $returnArray = parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->_unIndexDamType($obj);
        }
        return $returnArray;
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

    protected function _addReadableProperty ($obj)
    {
        
        // Set the workspace for old items in database
        if (! isset($obj['workspaces']) || $obj['workspaces'] == "") {
            $obj['workspaces'] = array(
                'global'
            );
        }
        
        $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
        
        if (! Manager::getService('Acl')->hasAccess("write.ui.damTypes") || (count(array_intersect($obj['workspaces'], $writeWorkspaces)) == 0 && ! in_array("all", $writeWorkspaces))) {
            $obj['readOnly'] = true;
        } else {
            $obj['readOnly'] = false;
        }
        return $obj;
    }

    /**
     * Push the dam type to Elastic Search
     *
     * @param array $obj            
     */
    protected function _indexDamType ($obj)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        
        $ElasticDataIndexService = Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexDamType($obj['id'], $obj, TRUE);
        
        $ElasticDataIndexService->indexByType('dam', $obj['id']);
        
        AbstractCollection::disableUserFilter($wasFiltered);
    }

    /**
     * Remove the content type from Indexed Search
     *
     * @param array $obj            
     */
    protected function _unIndexDamType ($obj)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteDamType($obj['id'], TRUE);
        
        AbstractCollection::disableUserFilter($wasFiltered);
    }
}
