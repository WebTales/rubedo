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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IPersonalPrefs;

/**
 * Service to handle PersonalPrefs
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class PersonalPrefs extends AbstractCollection implements IPersonalPrefs
{
	

	public function __construct(){
		$this->_collectionName = 'PersonalPrefs';
		parent::__construct();
		
		$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
		$currentUser = $currentUserService->getCurrentUserSummary();
		$this->_userId = $currentUser['id'];
	}
	
	public function create(array $obj, $options = array('safe'=>true)) {
    	$obj['userId']= $this->_userId;
        return parent::create($obj, $options);
    }
	
	public function getList($filters = null, $sort = null, $start = null, $limit = null){
		$this->_dataService->addFilter(array('userId' => $this->_userId));
		return parent::getList($filters, $sort, $start, $limit);
	}
	
	public function update(array $obj, $options = array('safe'=>true)){
		$this->_dataService->addFilter(array('userId' => $this->_userId));
		return parent::update($obj,$options);
	}
	
	public function destroy(array $obj, $options = array('safe'=>true)){
		$this->_dataService->addFilter(array('userId' => $this->_userId));
		return parent::destroy($obj,$options);
	}
}
