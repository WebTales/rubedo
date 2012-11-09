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
use Rubedo\Services\Manager;

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
	
	public function read(){
		$currentUserService = Manager::getService('currentUser');
		$currentUser = $currentUserService->getCurrentUserSummary();
		$userId = $currentUser['id'];
		$this -> _dataService -> addFilter(array('userId' => $result['id']));
		return parent::read();
	}
	
}
