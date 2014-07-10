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

use Rubedo\Interfaces\Collection\IWorkflowAbstractCollection;
use Rubedo\Services\Events;
use Rubedo\Services\Manager;


// require_once APPLICATION_PATH.'/../Core/Rubedo/Interfaces/Collection/IWorkflowAbstractCollection.php';

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class WorkflowAbstractCollection extends AbstractLocalizableCollection implements IWorkflowAbstractCollection
{
    const POST_PUBLISH_COLLECTION = 'rubedo_collection_publish_post';

    protected function _init()
    {
        if (empty($this->_collectionName)) {
            throw new \Rubedo\Exceptions\Server('Collection name is not set', "Exception97");
        }
        // init the data access service
        $this->_dataService = Manager::getService('MongoWorkflowDataAccess');
        $this->_dataService->init($this->_collectionName);
    }

    /**
     * Update an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::update
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function update(array $obj, $options = array(), $live = true)
    {
        if ($live === true) {
            $this->_dataService->setLive();
        } else {
            $this->_dataService->setWorkspace();
        }

        if (array_key_exists('status', $obj) && $obj['status'] == "pending") {
            $currentUser = Manager::getService('CurrentUser')->getCurrentUserSummary();
            $obj['lastPendingUser'] = $currentUser;
            $currentTime = Manager::getService('CurrentTime')->getCurrentTime();
            $obj['lastPendingTime'] = $currentTime;
        }

        $previousVersion = $this->findById($obj['id'], $live, false);
        $previousStatus = $previousVersion['status'];

        $returnArray = parent::update($obj, $options);
        if ($returnArray['success']) {
            if (!$live) {
                $transitionResult = $this->_transitionEvent($returnArray['data'], $previousStatus);
            }
        } else {
            $returnArray = array(
                'success' => false,
                'msg' => 'failed to update'
            );
        }

        return $returnArray;
    }

    /**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function create(array $obj, $options = array(), $live = false, $ignoreIndex = false)
    {
        if ($live === true) {
            throw new \Rubedo\Exceptions\Access('You can not create a content directly published', "Exception60");
        }

        $this->_dataService->setWorkspace();

        if (array_key_exists('status', $obj) && $obj['status'] == "pending") {
            $currentUser = Manager::getService('CurrentUser')->getCurrentUserSummary();
            $obj['lastPendingUser'] = $currentUser;
            $currentTime = Manager::getService('CurrentTime')->getCurrentTime();
            $obj['lastPendingTime'] = $currentTime;
        }

        $returnArray = parent::create($obj, $options);
        if ($returnArray['success']) {
            if (array_key_exists('status', $returnArray['data']) && $returnArray['data']['status'] === 'published') {
                $result = $this->publish($returnArray['data']['id'], $ignoreIndex);

                if (!$result['success']) {
                    $returnArray['success'] = false;
                    $returnArray['msg'] = "failed to publish the content";
                    unset($returnArray['data']);
                }
            } else {
                $transitionResult = $this->_transitionEvent($returnArray['data'], null);
            }
        } else {
            $returnArray = array(
                'success' => false,
                'msg' => 'failed to update'
            );
        }

        return $returnArray;
    }

    /**
     * Find an item given by its literral ID
     *
     * @param string $contentId
     * @return array
     */
    public function findById($contentId, $live = true, $raw = true)
    {
        if ($contentId === null) {
            return null;
        }
        if ($live === true) {
            $this->_dataService->setLive();
        } else {
            $this->_dataService->setWorkspace();
        }

        $obj = $this->_dataService->findById($contentId, $raw);
        if ($obj) {
            $obj = $this->_addReadableProperty($obj);
        }
        return $this->localizeOutput($obj);
        // return $obj;
    }

    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::getList()
     */
    public function getList(\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null, $live = true, $ismagic = null)
    {
        if ($live === true) {
            $this->_dataService->setLive();
        } else {
            $this->_dataService->setWorkspace();
        }
        $returnArray = parent::getList($filters, $sort, $start, $limit, $ismagic);

        return $returnArray;
    }

    /**
     * Find child of a node tree
     *
     * @param string $parentId
     *            id of the parent node
     * @param \WebTales\MongoFilters\IFilter $filters
     *            data filters
     * @param array $sort
     *            array of data sorts (mongo syntax)
     * @return array children array
     */
    public function readChild($parentId, \WebTales\MongoFilters\IFilter $filters = null, $sort = null, $live = true)
    {
        if ($live === true) {
            $this->_dataService->setLive();
        } else {
            $this->_dataService->setWorkspace();
        }

        $returnArray = parent::readChild($parentId, $filters, $sort);

        return $returnArray;
    }

    /**
     * Publish the collection
     *
     * @param string $objectId ID of the object
     * @param bool $ignoreIndex
     * @return mixed
     */
    public function publish($objectId, $ignoreIndex = false)
    {
        $result = $this->_dataService->publish($objectId);
        $args = $result;
        $args['data'] = array(
            'id' => $objectId,
            'ignoreIndex' => $ignoreIndex,
        );
        Events::getEventManager()->trigger(self::POST_PUBLISH_COLLECTION, $this, $args);
        return $result;
    }

    protected function _transitionEvent($obj, $previousStatus)
    {
        if (array_key_exists('status', $obj) && $obj['status'] === 'published') {
            $returnArray = array();
            $result = $this->publish($obj['id']);

            if (!$result['success']) {
                $returnArray['success'] = false;
                $returnArray['msg'] = "failed to publish the content";
                unset($returnArray['data']);
            }
        } elseif ($previousStatus != 'pending' && array_key_exists('status', $obj) && $obj['status'] == "pending") {
            $this->_notify($obj, 'pending');
        } else {
            $returnArray = null;
        }

        if ($previousStatus == 'pending' && array_key_exists('status', $obj) && $obj['status'] == 'refused') {
            $this->_notify($obj, 'refused');
        }
        if ($previousStatus == 'pending' && array_key_exists('status', $obj) && $obj['status'] == 'published') {
            $this->_notify($obj, 'published');
        }

        return true;
    }

    protected function _notify($obj, $notificationType)
    {
        return Manager::getService('Notification')->notify($obj, $notificationType);
    }
}
