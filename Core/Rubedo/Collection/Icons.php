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

use Rubedo\Interfaces\Collection\IIcons, Rubedo\Services\Manager;

/**
 * Service to handle Icons
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Icons extends AbstractCollection implements IIcons
{
    protected $_indexes = array(
        array('keys'=>array('userId'=>1)),
    );

	public function __construct(){
		$this->_collectionName = 'Icons';
		parent::__construct();
		
		$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
		$currentUser = $currentUserService->getCurrentUserSummary();
		$this->_userId = $currentUser['id'];
	}
	
    public function create(array $obj, $options = array()) {
    	$obj['userId']= $this->_userId;
        return parent::create($obj, $options);
    }
	
	public function getList($filters = null, $sort = null, $start = null, $limit = null){
		$this->_dataService->addFilter(array('userId' => $this->_userId));
		return parent::getList($filters, $sort, $start, $limit);
	}
	
	public function update(array $obj, $options = array()){
		$this->_dataService->addFilter(array('userId' => $this->_userId));
		return parent::update($obj,$options);
	}
	
	public function destroy(array $obj, $options = array()){
		$this->_dataService->addFilter(array('userId' => $this->_userId));
		return parent::destroy($obj,$options);
	}
	
	public function clearOrphanIcons() {
		$usersService = Manager::getService('Users');
		
		$result = $usersService->getList();
		
		//recovers the list of contentTypes id
		foreach ($result['data'] as $value) {
			$usersArray[] = $value['id'];
		}

		$result = $this->customDelete(array('userId' => array('$nin' => $usersArray)));
		
		if($result['ok'] == 1){
			return array('success' => 'true');
		} else {
			return array('success' => 'false');
		}
	}
	
	public function countOrphanIcons() {
		$usersService = Manager::getService('Users');

		$result = $usersService->getList();
		
		//recovers the list of contentTypes id
		foreach ($result['data'] as $value) {
			$usersArray[] = $value['id'];
		}
		
		return $this->count(array(array('property' => 'userId', 'operator' => '$nin', 'value' => $usersArray)));
	}
}
