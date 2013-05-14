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

use Rubedo\Interfaces\Collection\IPersonalPrefs, Rubedo\Services\Manager;

/**
 * Service to handle PersonalPrefs
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class PersonalPrefs extends AbstractCollection implements IPersonalPrefs
{
    protected $_indexes = array(
        array('keys'=>array('userId'=>1),'options'=>array('unique'=>true)),
    );

    public function __construct ()
    {
        $this->_collectionName = 'PersonalPrefs';
        parent::__construct();
        
        $currentUserService = Manager::getService('CurrentUser');
        $currentUser = $currentUserService->getCurrentUserSummary();
        $this->_userId = $currentUser['id'];
        
        $userFilter = new \WebTales\MongoFilters\ValueFilter();
        $userFilter->setName('userId')->setValue($this->_userId);
        $this->_dataService->addFilter($userFilter);
    }

    public function create (array $obj, $options = array())
    {
        if(!isset($obj['userId'])){
        	$obj['userId'] = $this->_userId;
		}
        return parent::create($obj, $options);
    }

    public function getList (\WebTales\MongoFilters\IFilter $filters = null, $sort = null, $start = null, $limit = null)
    {

        $returnArray = parent::getList($filters, $sort, $start, $limit);
        if ($returnArray['count'] == 1) {
            $iconSet = $returnArray['data'][0]['iconSet'];
            Manager::getService('Session')->set('iconSet', $iconSet);
        }
        
        return $returnArray;
    }

    public function update (array $obj, $options = array())
    {
        $returnArray = parent::update($obj, $options);
        if (isset($obj['iconSet'])) {
            Manager::getService('Session')->set('iconSet', $obj['iconSet']);
        }
        return $returnArray;
    }

    public function destroy (array $obj, $options = array())
    {
        return parent::destroy($obj, $options);
    }
	
	public function clearOrphanPrefs() {
	    $this->_dataService->clearFilter();
		$usersService = Manager::getService('Users');
		
		$result = $usersService->getList();
		
		foreach ($result['data'] as $value) {
			$usersArray[] = $value['id'];
		}

		$ninFilter = new \WebTales\MongoFilters\NotInFilter();
		$ninFilter->setName('userId')->setValue($usersArray);
		$result = $this->customDelete($ninFilter);
		
		if($result['ok'] == 1){
			return array('success' => 'true');
		} else {
			return array('success' => 'false');
		}
	}
	
	public function countOrphanPrefs() {
	    $this->_dataService->clearFilter();
		$usersService = Manager::getService('Users');

		$result = $usersService->getList();
		
		foreach ($result['data'] as $value) {
			$usersArray[] = $value['id'];
		}
		$ninFilter = new \WebTales\MongoFilters\NotInFilter();
		$ninFilter->setName('userId')->setValue($usersArray);
		return $this->count($ninFilter);
	}
}
