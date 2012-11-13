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

use Rubedo\Interfaces\Collection\IPanier;

/**
 * Service to handle Panier
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Panier extends AbstractCollection implements IPanier
{
	

	public function __construct(){
		$this->_collectionName = 'Panier';
		parent::__construct();
	}
	
}
