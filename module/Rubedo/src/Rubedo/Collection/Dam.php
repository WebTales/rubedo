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

use Rubedo\Interfaces\Collection\IDam, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

/**
 * Service to handle Groups
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Dam extends AbstractLocalizableCollection implements IDam
{
    protected static $nonLocalizableFields = array("Content-Type","typeId","taxonomy","fileSize","mainFileType","target","writeWorkspace","directory","readOnly","originalFileId");
    protected static $labelField = 'title';
    protected static $isLocaleFiltered = true;
    
    protected $_indexes = array(
        array(
            'keys' => array(
                'target' => 1,
                'createTime' => - 1
            )
        ),
        array(
            'keys' => array(
                'mainFileType' => 1,
                'target' => 1,
                'createTime' => - 1
            )
        ),
        array(
            'keys' => array(
                'originalFileId' => 1
            ),
            'options' => array(
                'unique' => true
            )
        )
    );

    /**
     * ensure that no nested contents are requested directly
     */
    protected function _init ()
    {
        parent::_init();
        
        if (! self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (! in_array('all', $readWorkspaceArray)) {
                $readWorkspaceArray[] = null;
                $readWorkspaceArray[] = 'all';
                
                $filter = Filter::factory('OperatorToValue')->setName('target')
                    ->setOperator('$in')
                    ->setValue($readWorkspaceArray);
                $this->_dataService->addFilter($filter);
            }
        }
    }

    public function __construct ()
    {
        $this->_collectionName = 'Dam';
        parent::__construct();
    }

    public function destroy (array $obj, $options = array())
    {
        $obj = $this->_dataService->findById($obj['id']);
            Manager::getService('Files')->destroy(array(
            'id' => $obj['originalFileId']
        ));
        
        $returnArray = parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->_unIndexDam($obj);
        }
        return $returnArray;
    }

    /**
     * Push the dam to Elastic Search
     *
     * @param array $obj            
     */
    protected function _indexDam ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexDam($obj);
    }

    /**
     * Remove the content from Indexed Search
     *
     * @param array $obj            
     */
    protected function _unIndexDam ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteDam($obj['typeId'], $obj['id']);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array())
    {
        $this->_filterInputData($obj);
        
        $originalFilePointer = Manager::getService('Files')->findById($obj['originalFileId']);
        if (! $originalFilePointer instanceof \MongoGridFSFile) {
            throw new \Rubedo\Exceptions\Server('no file found', "Exception8");
        }
        $obj['fileSize'] = $originalFilePointer->getSize();
        
        if (count(array_intersect(array(
            $obj['writeWorkspace']
        ), $obj['target'])) == 0) {
            $obj['target'][] = $obj['writeWorkspace'];
            $obj['fields']['target'][] = $obj['writeWorkspace'];
        }
        
        $returnArray = parent::update($obj, $options);
        
        if ($returnArray["success"]) {
            $this->_indexDam($returnArray['data']);
        }
        
        return $returnArray;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array(), $index = true)
    {
        $obj = $this->_setDefaultWorkspace($obj);
        
        $this->_filterInputData($obj);
        
        $originalFilePointer = Manager::getService('Files')->findById($obj['originalFileId']);
        if (! $originalFilePointer instanceof \MongoGridFSFile) {
            throw new \Rubedo\Exceptions\Server('no file found', "Exception8");
        }
        $obj['fileSize'] = $originalFilePointer->getSize();
        $returnArray = parent::create($obj, $options);
        
        if ($returnArray["success"] and $index) {
            $this->_indexDam($returnArray['data']);
        }
        
        return $returnArray;
    }

    public function getByType ($typeId)
    {
        $filter = Filter::factory('Value')->setName('typeId')->SetValue($typeId);
        return $this->getList($filter);
    }

    public function getListByDamTypeId ($typeId)
    {
        $filter = Filter::factory('Value')->setName('typeId')->SetValue($typeId);
        return $this->getList($filter);
    }

    /**
     * Set workspace if none given based on User main group.
     *
     * @param array $content            
     * @return array
     */
    protected function _setDefaultWorkspace ($dam)
    {
        if (! isset($dam['writeWorkspace']) || $dam['writeWorkspace'] == '' || $dam['writeWorkspace'] == array()) {
            $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
            $dam['writeWorkspace'] = $mainWorkspace['id'];
            $dam['fields']['writeWorkspace'] = $mainWorkspace['id'];
        } else {
            $readWorkspaces = array_values(Manager::getService('CurrentUser')->getReadWorkspaces());
            
            if (! in_array($dam['writeWorkspace'], $readWorkspaces) && $readWorkspaces[0] != "all") {
                throw new \Rubedo\Exceptions\Access('You don\'t have access to this workspace ', "Exception38");
            }
        }
        
        if (! isset($dam['target'])) {
            $dam['target'] = array();
        }
        
        if (! in_array($dam['writeWorkspace'], $dam['target'])) {
            $dam['target'][] = $dam['writeWorkspace'];
            $dam['fields']['target'][] = $dam['writeWorkspace'];
        }
        
        return $dam;
    }

    /**
     * Defines if each objects are readable
     *
     * @param array $obj
     *            Contain the current object
     * @return array
     */
    protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            // Set the workspace/target for old items in database
            if (! isset($obj['writeWorkspace']) || $obj['writeWorkspace'] == "" || $obj['writeWorkspace'] == array()) {
                $obj['writeWorkspace'] = "";
                $obj['fields']['writeWorkspace'] = "";
            }
            if (! isset($obj['target']) || $obj['target'] == "" || $obj['target'] == array()) {
                $obj['target'] = array(
                    'global'
                );
                $obj['fields']['target'] = array(
                    'global'
                );
            }
            
            $damTypeId = $obj['typeId'];
            $aclServive = Manager::getService('Acl');
            $damType = Manager::getService('DamTypes')->findById($damTypeId);
            
            if ($damType['readOnly'] || ! $aclServive->hasAccess("write.ui.dam")) {
                $obj['readOnly'] = true;
            } elseif (in_array($obj['writeWorkspace'], $writeWorkspaces) == false) {
                $obj['readOnly'] = true;
            } else {
                
                $obj['readOnly'] = false;
            }
        }
        
        return $obj;
    }

    protected function _filterInputData (array $obj, array $model = null)
    {
        if (! self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if (! in_array($obj['writeWorkspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace', "Exception36");
            }
            
            $readWorkspaces = Manager::getService('CurrentUser')->getReadWorkspaces();
            if ((! in_array('all', $readWorkspaces)) && count(array_intersect($obj['target'], $readWorkspaces)) == 0) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace', "Exception36");
            }
            
            $damTypeId = $obj['typeId'];
            $damType = Manager::getService('DamTypes')->findById($damTypeId);
            if (! in_array($obj['writeWorkspace'], $damType['workspaces']) && ! in_array('all', $damType['workspaces'])) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace', "Exception36");
            }
        }
        
        return parent::_filterInputData($obj, $model);
    }
    
    /**
     * Get the media type
     *
     * @param string $mediaId
     * @return The media type (video, image, document etc)
     */
    public function getMediaType($mediaId) {
        if (! $mediaId instanceof \MongoId) {
            if (! is_string($mediaId) || preg_match('/[\dabcdef]{24}/', $mediaId) !== 1) {
                throw new \Rubedo\Exceptions\User('Invalid MongoId :' . $mediaId);
            }
       }
        $media = $this->findById($mediaId);
        $damTypeId = $media["typeId"];
        
        $damType = Manager::getService("DamTypes")->findById($damTypeId);
        
        $mediaType = $damType["type"];
        
        return $mediaType;
    }
    
    public function updateVersionForFileId($fileId){
        $filters = Filter::factory();
        $filter = Filter::factory('Value')->SetName('originalFileId')->setValue($fileId);
        $filters->addFilter($filter);
        
        $options = array(
            'multiple' => true
        );
        $data = array('$inc'=>array('version'=>1));
        return $this->customUpdate($data, $filters,$options);
    }
    
    public function  findByTitle($title) {

        $filter = Filter::factory('Value')->SetName('title')->setValue($title);
        return $this->findOne($filter);
              
    }
    
    public function  findByOriginalFileId($id) {
    
        $filter = Filter::factory('Value')->SetName('originalFileId')->setValue($id);
        return $this->findOne($filter);
    
    }
    
}

