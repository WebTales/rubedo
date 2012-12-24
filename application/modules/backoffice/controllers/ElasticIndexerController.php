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
