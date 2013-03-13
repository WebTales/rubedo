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

use Rubedo\Interfaces\Collection\INestedContents;
use Rubedo\Services\Manager;

/**
 * Service to handle contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 * @todo not yet implemented
 */
class NestedContents implements INestedContents
{
    /**
     * name of the collection
     *
     * @var string
     */
    protected $_collectionName;

    /**
     * data access service
     *
     * @var \Rubedo\Mongo\DataAccess
     */
    protected $_dataService;

    /**
     * Set collection Name to Contents and init a mongo service with this collection
     */
    public function __construct() {
        // init the data access service
        $this->_collectionName = 'Contents';
        $this->_dataService = Manager::getService('MongoDataAccess');
        $this->_dataService->init($this->_collectionName);
    }

    /**
     * Do a find request on nested contents of a given content
     *
     * @param string $parentContentId parent id of nested contents
     * @return array
     */
    public function getList($parentContentId) {
        $cursor = $this->_dataService->customFind(array('_id' => $this->_dataService->getId($parentContentId)), array('nestedContents'));
        if ($cursor->count() == 0) {
            return array();
        }
        $content = $cursor->getNext();
        if (!isset($content['nestedContents'])) {
            return array();
        }
        return array_values($content['nestedContents']);
    }

    /**
     * Find a nested content by its id and its parentId
     *
     * @param string $parentContentId id of the parent content
     * @param string $subContentId id of the content we are looking for
     */
    public function findById($parentContentId, $subContentId) {
        $cursor = $this->_dataService->customFind(array('_id' => $this->_dataService->getId($parentContentId), 'nestedContents.id' => $subContentId), array('nestedContents.$'));
        if ($cursor->count() == 0) {
            return null;
        }
        $content = $cursor->getNext();
        return array_pop($content['nestedContents']);
    }

    /**
     * Create an objet in the current collection
     *
     * @param string $parentContentId parent id of nested contents
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function create($parentContentId, array $obj, $options = array('safe'=>true)) {
        $objId = $this->_dataService->getId();
        $obj['id'] = (string)$objId;

        unset($obj['parentContentId']);
		$obj['version'] = 1;

        $currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
        $currentUser = $currentUserService->getCurrentUserSummary();
        $obj['lastUpdateUser'] = $currentUser;
        $obj['createUser'] = $currentUser;

        $currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
        $currentTime = $currentTimeService->getCurrentTime();

        $obj['createTime'] = $currentTime;
        $obj['lastUpdateTime'] = $currentTime;

        $data = array('$push' => array('nestedContents' => $obj));
        $updateCond = array('_id' => $this->_dataService->getId($parentContentId));

        $returnArray = $this->_dataService->customUpdate($data, $updateCond);
        if ($returnArray['success'] == true) {
            $returnArray['data'] = $obj;
        }

        return $returnArray;
    }

    /**
     * Update an objet in the current collection
     *
     * @param string $parentContentId parent id of nested contents
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function update($parentContentId, array $obj, $options = array('safe'=>true)) {
        unset($obj['parentContentId']);

		$oldVersion = $obj['version'];
		
		$obj['version']++;

        $currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
        $currentUser = $currentUserService->getCurrentUserSummary();
        $obj['lastUpdateUser'] = $currentUser;

        $currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
        $currentTime = $currentTimeService->getCurrentTime();

        $obj['lastUpdateTime'] = $currentTime;

        $updateArray = array();

        foreach ($obj as $key => $value) {
            if (in_array($key, array('createUser', 'createTime'))) {
                continue;
            }
            $updateArray['nestedContents.$.' . $key] = $value;
        }

        $data = array('$set' => $updateArray);
        $updateCond = array('_id' => $this->_dataService->getId($parentContentId), 'nestedContents' => array('$elemMatch' => array('id'=>$obj['id'],'version'=>$oldVersion)));

        $returnArray = $this->_dataService->customUpdate($data, $updateCond);

        if ($returnArray['success'] == true) {
            unset($returnArray['data']);
        }

        return $returnArray;
    }

    /**
     * Delete objets in the current collection
     *
     * @param string $parentContentId parent id of nested contents
     * @param array $obj data object
     * @param bool $options should we wait for a server response
     * @return array
     */
    public function destroy($parentContentId, array $obj, $options = array('safe'=>true)) {

        $data = array('$pull' => array('nestedContents' => array('id' => $obj['id'])));
        $updateCond = array('_id' => $this->_dataService->getId($parentContentId));

        $returnArray = $this->_dataService->customUpdate($data, $updateCond);

        return array('success' => $returnArray['success']);
    }

}
