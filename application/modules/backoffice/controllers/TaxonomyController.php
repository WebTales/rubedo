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
		$this -> _dataTermsService = Rubedo\Services\Manager::getService('TaxonomyTerms');
	}
	
	public function deleteAction() {
		  $data = $this->getRequest()->getParam('data');
		    if (!is_null($data)) {
            $data = Zend_Json::decode($data);
            if (is_array($data)) {
				$deleteCond = array('vocabularyId'=>$data["id"]);
				$childDelete=$this->_dataTermsService->customDelete($deleteCond);
            }
			
			}
		if($childDelete["ok"]==1){ 
			parent::deleteAction();
		}
		
	}
}
