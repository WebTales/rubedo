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

use Rubedo\Interfaces\Collection\IIcons, Rubedo\Services\Manager, \WebTales\MongoFilters\Filter;

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
		
		$userFilter = Filter::Factory('Value');
		$userFilter->setName('userId')->setValue($this->_userId);
		$this->_dataService->addFilter($userFilter);
	}
	
    public function create(array $obj, $options = array()) {
    	$obj['userId']= $this->_userId;
        return parent::create($obj, $options);
    }
	
	
	
	public function clearOrphanIcons() {
	    $this->_dataService->clearFilter();
		$usersService = Manager::getService('Users');
		
		$result = $usersService->getList();
		
		foreach ($result['data'] as $value) {
			$usersArray[] = $value['id'];
		}

		$ninFilter = Filter::Factory('NotIn');
		$ninFilter->setName('userId')->setValue($usersArray);
		
		$result = $this->customDelete($ninFilter);
		
		if($result['ok'] == 1){
			return array('success' => 'true');
		} else {
			return array('success' => 'false');
		}
	}
	
	public function countOrphanIcons() {
	    $this->_dataService->clearFilter();
		$usersService = Manager::getService('Users');

		$result = $usersService->getList();
		
		foreach ($result['data'] as $value) {
			$usersArray[] = $value['id'];
		}
		
		$ninFilter = Filter::Factory('NotIn');
		$ninFilter->setName('userId')->setValue($usersArray);
		
		return $this->count($ninFilter);
	}
}
