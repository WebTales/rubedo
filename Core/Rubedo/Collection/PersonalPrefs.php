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
    }

    public function create (array $obj, $options = array('safe'=>true))
    {
        if(!isset($obj['userId'])){
        	$obj['userId'] = $this->_userId;
		}
        return parent::create($obj, $options);
    }

    public function getList ($filters = null, $sort = null, $start = null, $limit = null)
    {
        $this->_dataService->addFilter(array(
            'userId' => $this->_userId
        ));
        $returnArray = parent::getList($filters, $sort, $start, $limit);
        if ($returnArray['count'] == 1) {
            $iconSet = $returnArray['data'][0]['iconSet'];
            Manager::getService('Session')->set('iconSet', $iconSet);
        }
        
        return $returnArray;
    }

    public function update (array $obj, $options = array('safe'=>true))
    {
        $this->_dataService->addFilter(array(
            'userId' => $this->_userId
        ));
        $returnArray = parent::update($obj, $options);
        if (isset($obj['iconSet'])) {
            Manager::getService('Session')->set('iconSet', $obj['iconSet']);
        }
        return $returnArray;
    }

    public function destroy (array $obj, $options = array('safe'=>true))
    {
        $this->_dataService->addFilter(array(
            'userId' => $this->_userId
        ));
        return parent::destroy($obj, $options);
    }
	
	public function clearOrphanPrefs() {
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
	
	public function countOrphanPrefs() {
		$usersService = Manager::getService('Users');

		$result = $usersService->getList();
		
		//recovers the list of contentTypes id
		foreach ($result['data'] as $value) {
			$usersArray[] = $value['id'];
		}
		
		return $this->count(array(array('property' => 'userId', 'operator' => '$nin', 'value' => $usersArray)));
	}
}
