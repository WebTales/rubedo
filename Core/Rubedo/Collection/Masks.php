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

use Rubedo\Interfaces\Collection\IMasks;

/**
 * Service to handle Users
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Masks extends AbstractCollection implements IMasks
{
	

	public function __construct(){
		$this->_collectionName = 'Masques';
		parent::__construct();
	}
	
}
