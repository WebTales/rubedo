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
namespace Rubedo\Mongo;

use Rubedo\Interfaces\Mongo\IWorkflowDataAccess;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class WorkflowDataAccess extends DataAccess implements IWorkflowDataAccess
{
    /**
     * Contain the current workspace
     */
    protected $_currentWs = "live";

    /**
     * Contain common fields
     */
    protected $_metaDataFields = array('_id', 'id', 'idLabel', 'typeId', 'createTime', 'createUser', 'lastUpdateTime', 'lastUpdateUser', 'version', 'online', 'text');

    /**
     * Changes the array to obtain workspace and live blocks
     *
     * @param $obj is an array
     * @return array
     */
    protected function _inputObjectFilter($obj) {
        foreach ($obj as $key => $value) {
            if (in_array($key, $this->_metaDataFields)) {
                continue;
            }
            $obj[$this->_currentWs][$key] = $value;
            unset($obj[$key]);
        }

        return $obj;
    }

    /**
     * Changes the array to keep the response usable by the BO
     *
     * @param $obj is an array
     * @return array
     */
    protected function _outputObjectFilter($obj) {
		if($obj['workspace']['status'] == "draft" && $this->_currentWs == "live") {
            $this->_currentWs = "workspace";
        }
		
        if (isset($obj[$this->_currentWs])) {
            foreach ($obj[$this->_currentWs] as $key => $value) {
                $obj[$key] = $value;
            }
            unset($obj['live']);
            unset($obj['workspace']);
        }

        return $obj;
    }

    /**
     * Adapt filter for the workflow
     *
     * @param $filter is the current filter
     * @return array compatible with the data in mongoDb
     */
    protected function _adaptFilter($filterArray) {

        if (count($filterArray) > 0) {
            $this->clearFilter();

            foreach ($filterArray as $key => $value) {
                if (in_array($key, $this->_metaDataFields)) {
                    $this->addFilter(array($key => $value));
                    continue;
                }
                $newKey = $this->_currentWs . "." . $key;
                $this->addFilter(array($newKey => $value));
            }
        }
    }

    /**
     * Adapt sort for the workflow
     *
     * @param $sort is the current sort
     * @return array compatible with the data in mongoDb
     */
    protected function _adaptSort($sortArray) {
        if (count($sortArray) != 0) {
            $this->clearSort();

            foreach ($sortArray as $key => $value) {
            	 if ($key == '_id') {
                	$this->addSort(array('id' => (string)$value));
                    continue;
                }
                if (in_array($key, $this->_metaDataFields)) {
                    	$this->addSort(array($key => $value));
                    continue;
                }
                $newKey = $this->_currentWs . "." . $key;
                $this->addSort(array($newKey => $value));
                unset($sortArray[$key]);
            }
        }
    }

    /**
     * Adapt fields for the workflow
     *
     * @param $fieldsArray is the current included fields
     * @return array compatible with the data in mongoDb
     */
    protected function _adaptFields($fieldsArray) {
        if (count($fieldsArray) != 0) {
            $this->clearFieldList();
            $newArray = array();

            foreach ($fieldsArray as $key => $value) {
                if (in_array($key, $this->_metaDataFields)) {
                    $newArray[] = $key;
                } else {
                    $newKey = $this->_currentWs . "." . $key;
                    $newArray[] = $newKey;
                }
            }

            unset($fieldsArray);
            $this->addToFieldList($newArray);
        }
    }

    /**
     * Adapt excluded fields for the workflow
     *
     * @param $fieldsArray is the current excluded fields
     * @return array compatible with the data in mongoDb
     */
    protected function _adaptExcludeFields($fieldsArray) {
        if (count($fieldsArray) != 0) {
            $this->clearExcludeFieldList();
            $newArray = array();

            foreach ($fieldsArray as $key => $value) {
                if (in_array($key, $this->_metaDataFields)) {
                    $newArray[] = $key;
                } else {
                    $newKey = $this->_currentWs . "." . $key;
                    $newArray[] = $newKey;
                }
            }

            unset($fieldsArray);
            $this->addToExcludeFieldList($newArray);
        }
    }

    /**
     * Set the current workspace to workspace
     */
    public function setWorkspace() {
        $this->_currentWs = 'workspace';
    }

    /**
     * Set the current workspace to live
     */
    public function setLive() {
        $this->_currentWs = 'live';
    }

    /**
     * Publish a content
     */
    public function publish($objectId) {
        $versioningService = \Rubedo\Services\Manager::getService('Versioning');
        $obj = $this->_collection->findOne(array('_id' => $this->getId($objectId)));

        if (isset($obj['workspace'])) {
            //define the publish values for the version handling
            $version = $obj;

            //copy the workspace into the live
            $obj['live'] = $obj['workspace'];

            $updateCond = array('_id' => $this->getId($objectId));

            //update the content with the new values for the live array
            $returnArray = $this->customUpdate($obj, $updateCond);

            //if the update is ok, the previous version of the live is stored in Versioning collection
            if ($returnArray['success']) {
                $result = $versioningService->addVersion($version);
                if (!$result) {
                    $returnArray['success'] = false;
                    unset($returnArray['data']);
                }
            } else {
                $returnArray = array('success' => false, 'msg' => 'failed to update the version');
            }
        } else {
            $returnArray = array('success' => false, 'msg' => 'failed to publish');
        }

        return $returnArray;
    }

    /**
     * Allow to read in the current collection
     *
     * @return array
     */
    public function read() {
        //Adaptation of the conditions for the workflow
        $filter = $this->getFilterArray();
        $this->_adaptFilter($filter);
        $sort = $this->getSortArray();
        $this->_adaptSort($sort);
        $includedFields = $this->getFieldList();
        $this->_adaptFields($includedFields);
        $excludedFields = $this->getExcludeFieldList();
        $this->_adaptExcludeFields($excludedFields);

        $content = parent::read();
		$count = $content['count'];
		$content = $content['data'];
		
        foreach ($content as $key => $value) {
            $content[$key] = $this->_outputObjectFilter($value);
        }

        return array('count'=>$count,'data'=>$content);
    }

    /**
     * Allow to update an element in the current collection
     *
     * @return bool
     */
    public function update(array $obj, $options = array('safe'=>true)) {
        $obj = $this->_inputObjectFilter($obj);

        $result = parent::update($obj, $options);

        if ($result['success']) {
            $result['data'] = $this->_outputObjectFilter($result['data']);
            return $result;
        } else {
            $result = array('success' => false);
            return $result;
        }

    }

    /**
     * Allow to create an item in the current collection
     *
     * @return array
     */
    public function create(array $obj, $options = array('safe'=>true)) {
        $obj = $this->_inputObjectFilter($obj);

        if ($this->_currentWs === 'workspace') {
            $obj['live'] = array();
        } else {
            $obj['workspace'] = array();
        }
        $result = parent::create($obj, $options);

        $result['data'] = $this->_outputObjectFilter($result['data']);

        return $result;
    }

    /**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function destroy(array $obj, $options = array('safe'=>true)) {
        $result = parent::destroy($obj, $options);

        return $result;
    }
	
	/**
     * Find an item given by its literral ID
     * @param string $contentId
     * @return array
     */
    public function findById($contentId,$raw=true) {
        return $this->findOne(array('_id' => $this->getId($contentId)),$raw);
    }
	
	public function findOne($value,$raw=true){
		if($raw){
			return parent::findOne($value);
		}
        //Adaptation of the conditions for the workflow
        $filter = $this->getFilterArray();
        $this->_adaptFilter($filter);
        $sort = $this->getSortArray();
        $this->_adaptSort($sort);
        $includedFields = $this->getFieldList();
        $this->_adaptFields($includedFields);
        $excludedFields = $this->getExcludeFieldList();
        $this->_adaptExcludeFields($excludedFields);

        $data = parent::findOne($value);
		
		return $this->_outputObjectFilter($data);
	}

}
