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

use Rubedo\Interfaces\Collection\IDirectories;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

/**
 * Service to handle Directories
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 */
class Directories extends AbstractCollection implements IDirectories
{

    protected $_indexes = array(
        array(
            'keys' => array(
                'filePlan' => 1,
                'parentId' => 1,
                'orderValue' => 1
            )
        ),
        array(
            'keys' => array(
                'filePlan' => 1,
                'parentId' => 1,
                'workspace' => 1,
                'orderValue' => 1
            )
        ),
        array(
            'keys' => array(
                'text' => 1,
                'parentId' => 1,
                'filePlan' => 1
            ),
            'options' => array(
                'unique' => true
            )
        )
    );

    protected $_model = array(
        'text' => array(
            'domain' => 'string',
            'required' => true
        ),
        'filePlan' => array(
            'domain' => 'string',
            'required' => true
        ),
        'orderValue' => array(
            'domain' => 'integer',
            'required' => true
        ),
        'expandable' => array(
            'domain' => 'bool',
            'required' => true
        ),
        'workspace' => array(
            'domain' => 'string',
            'required' => true
        ),
        'inheritWorkspace' => array(
            'domain' => 'bool',
            'required' => true
        )
    );

    protected $_virtualNotFiledDirectory = array(
        "parentId" => 'root',
        "id" => "notFiled",
        "expandable" => false,
        "readOnly" => false,
        "orderValue" => 1,
        "workspace" => 'global'
    );

    /**
     * Only access to content with read access
     *
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init()
    {
        parent::_init();

        if (!self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (in_array('all', $readWorkspaceArray)) {
                return;
            }
            $filter = Filter::factory('In');
            $filter->setName('workspace')->setValue($readWorkspaceArray);
            $this->_dataService->addFilter($filter);
        }
    }

    public function __construct()
    {
        $this->_collectionName = 'Directories';
        parent::__construct();
    }

    public function readChild($parentId, \WebTales\MongoFilters\IFilter $filters = null, $sort = null)
    {
        if (!$parentId) {
            return array();
        }
        if (isset($sort)) {
            foreach ($sort as $value) {
                $this->_dataService->addSort(array(
                    $value["property"] => strtolower($value["direction"])
                ));
            }
        } else {
            $this->_dataService->addSort(array(
                "orderValue" => 1
            ));
        }

        $result = $this->_dataService->readChild($parentId, $filters);
        if ($result && is_array($result)) {
            foreach ($result as &$obj) {
                $obj = $this->_addReadableProperty($obj);
            }
        }
        if ($parentId == "root") {
            $result[] = $this->_virtualNotFiledDirectory;
        }
        return $result;
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
    public function destroy(array $obj, $options = array())
    {
        $deleteCond = Filter::factory('InUid')->setValue($this->_getChildToDelete($obj['id']));

        $resultArray = $this->_dataService->customDelete($deleteCond);

        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] > 0) {
                $returnArray = array(
                    'success' => true
                );
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'La suppression du dossier a échoué'
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
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update(array $obj, $options = array())
    {
        $obj = $this->_initContent($obj);

        $returnValue = parent::update($obj, $options);

        $this->propagateWorkspace($obj['id'], $obj['workspace']);

        return $returnValue;
    }

    /**
     * Set workspace
     *
     * @param array $obj
     * @throws \Exception
     * @return array
     */
    protected function _initContent($obj)
    {

        // set inheritance for workspace
        if (!isset($obj['inheritWorkspace']) || $obj['inheritWorkspace'] !== false) {
            $obj['inheritWorkspace'] = true;
        }
        // resolve inheritance if not forced
        if ($obj['inheritWorkspace']) {
            unset($obj['workspace']);

            if ($obj['parentId'] == "root") {
                $obj['workspace'] = "global";
            } else {
                $ancestorsLine = array_reverse($this->getAncestors($obj));
                $notFound = true;
                foreach ($ancestorsLine as $ancestor) {
                    if (isset($ancestor['inheritWorkspace']) && $ancestor['inheritWorkspace'] == false) {
                        $obj['workspace'] = $ancestor['workspace'];
                        $notFound = false;
                        break;
                    }
                }
                if ($notFound) {
                    $obj['workspace'] = "global";
                }
            }

        }
        // verify workspace can be attributed
        if (!self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();

            if (!in_array($obj['workspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access('You can not assign page to this workspace', "Exception48");
            }
        }


        return $obj;
    }


    public function getListByFilePlanId($filePlanId)
    {
        $filters = Filter::factory('Value')->setName('filePlan')->setValue($filePlanId);
        return $this->getList($filters);
    }

    public function create(array $obj, $options = array())
    {
        $obj = $this->_initContent($obj);
        $result = parent::create($obj, $options);
        return $result;
    }


    public function deleteByFilePlanId($id)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        $filters = Filter::factory('Value')->setName('filePlan')->setValue($id);
        $result = $this->_dataService->customDelete($filters);

        AbstractCollection::disableUserFilter($wasFiltered);

        return $result;
    }

    public function clearOrphanDirectories()
    {

        $filePlansArray = array("default");
        $filters = Filter::factory('NotIn')->setName('filePlan')->setValue($filePlansArray);
        $result = $this->customDelete($filters);
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

    public function countOrphanDirectories()
    {
        $filePlansArray = array("default");
        $filters = Filter::factory('NotIn')->setName('filePlan')->setValue($filePlansArray);
        return $this->count($filters);
    }

    protected function _addReadableProperty($obj)
    {
        if (!self::isUserFilterDisabled()) {
            // Set the workspace for old items in database
            if (!isset($obj['workspace'])) {
                $obj['workspace'] = 'global';
            }

            $aclServive = Manager::getService('Acl');
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();

            if (!in_array($obj['workspace'], $writeWorkspaces) || !$aclServive->hasAccess("write.ui.directories")) {
                $obj['readOnly'] = true;
            } else {
                $obj['readOnly'] = false;
            }
        }

        return $obj;
    }


    public function propagateWorkspace($parentId, $workspaceId, $filePlanId = null)
    {
        $filters = Filter::factory();
        if ($filePlanId) {
            $filters = Filter::factory('Value')->setName('filePlan')->setValue($filePlanId);
        }
        $pageList = $this->readChild($parentId, $filters);
        foreach ($pageList as $page) {
            if (!self::isUserFilterDisabled()) {
                if (!$page['readOnly']) {
                    if ($page['workspace'] != $workspaceId) {
                        $this->update($page);
                    }
                }
            } else {
                if ($page['workspace'] != $workspaceId) {
                    $this->update($page);
                }
            }
        }
    }

    /**
     *
     * @param string $id
     *            id whose children should be deleted
     * @return array array list of items to delete
     */
    protected function _getChildToDelete($id)
    {
        // delete at least the node
        $returnArray = array(
            $this->_dataService->getId($id)
        );

        // read children list
        $terms = $this->readChild($id);

        // for each child, get sublist of children
        if (is_array($terms)) {
            foreach ($terms as $value) {
                $returnArray = array_merge($returnArray, $this->_getChildToDelete($value['id']));
            }
        }

        return $returnArray;
    }

    /**
     * Set the directory for dam items given by an array of ID
     *
     * @param unknown $arrayId
     * @param unknown $directoryId
     * @throws Rubedo\Exceptions\User
     */
    public function classify($arrayId, $directoryId)
    {
        if (!is_string($directoryId)) {
            throw new \Rubedo\Exceptions\User('%1$s only accept string parameter', "Exception88", 'classify');
        }
        $data = array(
            '$set' => array(
                'directory' => $directoryId
            )
        );
        $updateCond = Filter::factory('InUid')->setValue($arrayId);

        $damService = Manager::getService('Dam');
        $directoryFrom = null;
        $damList = $damService->getList($updateCond);
        if (count($damList['data']) > 0) {
            foreach ($damList['data'] as $damItem) {
                if (isset($damItem['directory'])) {
                    if (is_null($directoryFrom)) {
                        $directoryFrom = $damItem['directory'];
                    }
                    if ($damItem['directory'] != $directoryFrom) {
                        throw new \Rubedo\Exceptions\Access('only one source folder allowed');
                    }
                }
            }
        }

        $directoryFrom = $this->findById($directoryFrom);

        $writeWorkspaceArray = Manager::getService('CurrentUser')->getWriteWorkspaces();
        if (!in_array($directoryFrom['workspace'], $writeWorkspaceArray)) {
            throw new \Rubedo\Exceptions\Access("can't move media from this directory due to insufficient rights");
        }

        $directoryTo = $this->findById($directoryId);

        if (!in_array($directoryTo['workspace'], $writeWorkspaceArray)) {
            throw new \Rubedo\Exceptions\Access("can't move media to this directory due to insufficient rights");
        }
        $options = array(
            'multiple' => true
        );
        return $damService->customUpdate($data, $updateCond, $options);
    }

    /* (non-PHPdoc)
     * @see \Rubedo\Collection\AbstractCollection::findById()
     */
    public function findById($contentId, $forceReload = false)
    {
        if ($contentId === null) {
            return null;
        }
        if ($contentId == "notFiled") {
            return $this->_virtualNotFiledDirectory;
        } else {
            return parent::findById($contentId, $forceReload);
        }

    }


}
