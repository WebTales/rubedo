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
 * Controller providing Elastic Search indexation
 *
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_ElasticIndexerController extends Zend_Controller_Action
{
    
	



	public function indexAction() {
		$es = Rubedo\Services\Manager::getService('ElasticDataIndex');
		$es->init();		
		$return = $es->indexAllContent() ;
		$this->_helper->json($return);
		
	}
	

	

}
