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

/**
 * Service to handle contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
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
     * @param array $filters filter the list with mongo syntax
     * @param array $sort sort the list with mongo syntax
     * @return array
     */
    public function getList($parentContentId, $filters = null, $sort = null) {

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
    }

}
