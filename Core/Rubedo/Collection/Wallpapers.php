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

use Rubedo\Interfaces\Collection\IWallpapers;

/**
 * Service to handle Wallpapers
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Wallpapers extends AbstractCollection implements IWallpapers
{
	

	public function __construct(){
		$this->_collectionName = 'Wallpapers';
		parent::__construct();
	}
	
}
