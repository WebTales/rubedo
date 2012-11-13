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

use Rubedo\Interfaces\Collection\IFieldTypes;

/**
 * Service to handle FieldTypes
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class FieldTypes extends AbstractCollection implements IFieldTypes
{
	

	public function __construct(){
		$this->_collectionName = 'FieldTypes';
		parent::__construct();
	}
	
}
