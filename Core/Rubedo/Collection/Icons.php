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

use Rubedo\Interfaces\Collection\IIcons;

/**
 * Service to handle Icons
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Icons extends AbstractCollection implements IIcons
{
	

	public function __construct(){
		$this->_collectionName = 'Icons';
		parent::__construct();
		
		$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
		$currentUser = $currentUserService->getCurrentUserSummary();
		$this->_userId = $currentUser['id'];
	}
	
    public function create(array $obj, $safe = true) {
    	$obj['userId']= $this->_userId;
        return parent::create($obj, $safe);
    }
	
	public function getList($filters = null, $sort = null, $start = null, $limit = null){
		$this->_dataService->addFilter(array('userId' => $this->_userId));
		return parent::getList($filters, $sort, $start, $limit);
	}
	
	public function update(array $obj, $safe = true){
		$this->_dataService->addFilter(array('userId' => $this->_userId));
		return parent::update($obj,$safe);
	}
	
	public function destroy(array $obj, $safe = true){
		$this->_dataService->addFilter(array('userId' => $this->_userId));
		return parent::destroy($obj,$safe);
	}
}
