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

use Rubedo\Interfaces\Collection\IThemes;

/**
 * Service to handle Themes
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Themes extends AbstractCollection implements IThemes
{
	

	public function __construct(){
		$this->_collectionName = 'Themes';
		parent::__construct();
	}
	
}
