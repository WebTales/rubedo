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

require_once('DataAccessController.php'); 
 
/**
 * Controller providing CRUD API for the taxonomy JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_TaxonomyController extends Backoffice_DataAccessController
{
	
	public function init(){
		parent::init();
		
		$this -> _dataService = Rubedo\Services\Manager::getService('Taxonomy');
	}

}
