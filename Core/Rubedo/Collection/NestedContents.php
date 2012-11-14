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
	
	protected function _init() {
        // init the data access service
        $this->_collectionName = 'Contents';
        $this->_dataService = Manager::getService('MongoDataAccess');
        $this->_dataService->init($this->_collectionName);
    }

    public function __construct() {
        $this->_init();
    }
	
	/**
     * Do a find request on nested contents of a given content
     *
	 * @param string $parentContentId parent id of nested contents
	 * @param array $filters filter the list with mongo syntax
	 * @param array $sort sort the list with mongo syntax
     * @return array
     */
    public function getList($parentContentId,$filters = null, $sort = null){
    	
		
    }
	
}
