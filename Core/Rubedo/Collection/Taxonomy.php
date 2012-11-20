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

use Rubedo\Interfaces\Collection\ITaxonomy;
use Rubedo\Services\Manager;

/**
 * Service to handle Taxonomy
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Taxonomy extends AbstractCollection implements ITaxonomy {

    public function __construct() {
        $this->_collectionName = 'Taxonomy';
        parent::__construct();
    }

    /**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function destroy(array $obj, $safe = true) {
        $termsService = Manager::getService('TaxonomyTerms');
        $returnArray = $termsService->customDelete(array('vocabularyId' => $obj['id']));
        if ($returnArray['ok'] == 1 && $returnArray['n'] > 0) {
            $result = parent::destroy($obj, $safe);
			if($result['success']==true){
				$response = array('success' => true);
			} else {
				$response = array('success' => false);
			}
			
        } else {
        	$returnArray = array('success' => false);
        }
		
		return $result;
    }

}
