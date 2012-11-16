<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
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
     * @var\Rubedo\Mongo\DataAccess
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
        $cursor = $this->_dataService->customFind(array('_id' => $this->_dataService->getId($parentContentId)), array('nestedContents.' . $subContentId));
        if ($cursor->count() == 0) {
            return null;
        }
        $content = $cursor->getNext();
        if (!isset($content['nestedContents'])) {
            return null;
        }
        //\Zend_Debug::dump($content['nestedContents']);
        return array_pop($content['nestedContents']);
    }

    /**
     * Create an objet in the current collection
     *
     * @param string $parentContentId parent id of nested contents
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function create($parentContentId, array $obj, $safe = true) {
        $objId = $this->_dataService->getId();
        $obj['id'] = (string)$objId;

        unset($obj['parentContentId']);
        unset($obj['version']);

        $currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
        $currentUser = $currentUserService->getCurrentUserSummary();
        $obj['lastUpdateUser'] = $currentUser;
        $obj['createUser'] = $currentUser;

        $currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
        $currentTime = $currentTimeService->getCurrentTime();

        $obj['createTime'] = $currentTime;
        $obj['lastUpdateTime'] = $currentTime;

        $data = array('$set' => array('nestedContents.' . (string)$objId => $obj));
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
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function update($parentContentId, array $obj, $safe = true) {
        $subContent = $this->findById($parentContentId, $obj['id']);
        if (!isset($subContent)) {
            return array('success' => false, 'msg' => 'can\'t find previous version');
        }

        unset($obj['parentContentId']);
        unset($obj['version']);

        $currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
        $currentUser = $currentUserService->getCurrentUserSummary();
        $obj['lastUpdateUser'] = $currentUser;
        $obj['createUser'] = $subContent['createUser'];

        $currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
        $currentTime = $currentTimeService->getCurrentTime();

        $obj['createTime'] = $subContent['createTime'];
        $obj['lastUpdateTime'] = $currentTime;

        $data = array('$set' => array('nestedContents.' . $obj['id'] => $obj));
        $updateCond = array('_id' => $this->_dataService->getId($parentContentId));

        $returnArray = $this->_dataService->customUpdate($data, $updateCond);

        if ($returnArray['success'] == true) {
            $returnArray['data'] = $obj;
        }

        return $returnArray;
    }

    /**
     * Delete objets in the current collection
     *
     * @param string $parentContentId parent id of nested contents
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function destroy($parentContentId, array $obj, $safe = true) {

        $data = array('$unset' => array('nestedContents.' . $obj['id'] => true));
        $updateCond = array('_id' => $this->_dataService->getId($parentContentId));

        $returnArray = $this->_dataService->customUpdate($data, $updateCond);

        return array('success' => $returnArray['success']);
    }

}
