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

use Rubedo\Interfaces\Collection\IClear;

/**
 * Service to handle Users
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Clear extends AbstractCollection implements IClear
{

	public function __construct(){
		$this->_collectionName = 'TaxonomyTerms';
		parent::__construct();
	}
	
	public function clearOrphanTerms() {
		$taxonomy = \Rubedo\Services\Manager::getService('Taxonomy');
		$orphans = array();
		$erreur = false;
		
		$terms = $this->getList();
		$terms = $terms ['data'];
		
		foreach ($terms as $value) {
			if(isset($value['vocabularyId'])){
				$vocabulary = $taxonomy->findById($value['vocabularyId']);
				
				if(!$vocabulary){
					$orphans[] = $value;
				} else {
					if (isset($value['parentId'])) {
						if(!$value['parentId'] == "root") {
							$parent = $this->findById($value['parentId']);
							
							if(!$parent){
								$orphans[] = $value;
							}
						}
					} else {
						$orphans[] = $value;
					}
				}
			} else {
				$orphans[] = $value;
			}
		}
		
		foreach ($orphans as $value) {
			$result = $this->destroy($value);

			if(!$result['success']){
				$erreur = true;
			}
		}
				
		if(!$erreur) {
			$response['success'] = true;
			$response['data'] = $orphans;
		} else {
			$response['success'] = false;
		}
		
		return $response;
	}
	
	public function update(array $obj, $options = array('safe'=>true)) {
		$response = array('success' => false, 'msg' => 'Forbiden');
		
		return $response;
	}
	
	public function create(array $obj, $options = array('safe'=>true)) {
		$response = array('success' => false, 'msg' => 'Forbiden');
		
		return $response;
	}
	
	public function customDelete($deleteCond, $options = array('safe'=>true)) {
		$response = array('success' => false, 'msg' => 'Forbiden');
		
		return $response;
	}
	
	public function customUpdate(array $data, array $updateCond, $options = array('safe'=>true)) {
		$response = array('success' => false, 'msg' => 'Forbiden');
		
		return $response;
	}
	
}
