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

use Rubedo\Interfaces\Collection\ITaxonomy;

/**
 * Service to handle Taxonomy
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Taxonomy extends AbstractCollection implements ITaxonomy
{
	

	public function __construct(){
		$this->_collectionName = 'Taxonomy';
		parent::__construct();
	}
	
}
