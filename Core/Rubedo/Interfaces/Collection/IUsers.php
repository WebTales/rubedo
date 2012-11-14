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
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling users
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IUsers extends IAbstractCollection{
	
	/**
	 * Change the password of the user given by its id
	 * Check version conflict
	 * 
	 * @param string $$password new password
	 * @param int $version version number
	 * @param string $userId id of the user to be changed
	 */
	public function changePassword($password,$version,$userId);
}
