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

use Rubedo\Interfaces\Collection\ISites;

/**
 * Service to handle Sites
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Sites extends AbstractCollection implements ISites
{
	

	public function __construct(){
		$this->_collectionName = 'Sites';
		parent::__construct();
	}
	
}
