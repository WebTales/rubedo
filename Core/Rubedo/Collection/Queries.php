<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IQueries,Rubedo\Services\Manager;

/**
 * Service to handle Queries
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Queries extends AbstractCollection implements IQueries
{
    protected $_indexes = array(
        array('keys'=>array('type'=>1)),
    );
    

	public function __construct(){
		$this->_collectionName = 'Queries';
		parent::__construct();
	}
	
}
