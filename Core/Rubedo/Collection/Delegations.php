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

use Rubedo\Interfaces\Collection\IDelegations;

/**
 * Service to handle Delegations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Delegations extends AbstractCollection implements IDelegations
{
	

	public function __construct(){
		$this->_collectionName = 'Delegations';
		parent::__construct();
	}
	
}
