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
	}
	
	public function getList($filters = null, $sort = null){
		$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
		$currentUser = $currentUserService->getCurrentUserSummary();
		$userId = $currentUser['id'];
		$this->_dataService->addFilter(array('userId' => $userId));
		return parent::getList($filters, $sort);
	}
	
}
