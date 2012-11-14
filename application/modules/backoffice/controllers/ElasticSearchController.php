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


 
/**
 * Controller providing Elastic Search querying
 *
 *
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_ElasticSearchController extends Zend_Controller_Action
{
    
	public function indexAction() {
		$es = Rubedo\Services\Manager::getService('ElasticDataSearch');
		$es->init();		
		$return = $es->search($this->_request->getPost('query')."*") ;
		$this->_helper->json($return);
		
	}

}
