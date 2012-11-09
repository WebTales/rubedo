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

use Rubedo\Interfaces\Collection\IContentTypes;

/**
 * Service to handle ContentTypes
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ContentTypes extends AbstractCollection implements IContentTypes
{
	

	public function __construct(){
		$this->_collectionName = 'ContentTypes';
		parent::__construct();
	}
	
}
