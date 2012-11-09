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

use Rubedo\Interfaces\Collection\IMedias;

/**
 * Service to handle Medias
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Medias extends AbstractCollection implements IMedias
{
	

	public function __construct(){
		$this->_collectionName = 'Medias';
		parent::__construct();
	}
	
}
