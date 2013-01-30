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

use Rubedo\Interfaces\Collection\IDam;
use Rubedo\Services\Manager;

/**
 * Service to handle Groups
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Dam extends AbstractCollection implements IDam
{

    public function __construct ()
    {
        $this->_collectionName = 'Dam';
        parent::__construct();
    }

    public function destroy (array $obj, $options = array('safe'=>true))
    {
        $obj = $this->_dataService->findById($obj['id']);
        $destroyOriginal = Manager::getService('Files')->destroy(array(
            'id' => $obj['originalFileId']
        ));
        
        $returnArray = parent::destroy($obj, $options);
        if ($returnArray["success"]) {
            $this->_unIndexDam($obj);
        }
        return $returnArray;
    }

    /**
     * Push the dam to Elastic Search
     *
     * @param array $obj            
     */
    protected function _indexDam ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexDam($obj['id']);
    }

    /**
     * Remove the content from Indexed Search
     *
     * @param array $obj            
     */
    protected function _unIndexDam ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService('ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteDam($obj['typeId'], $obj['id']);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array('safe'=>true,))
    {
//         if(!isset($obj['taxonomy']['navigation']) || empty($obj['taxonomy']['navigation'])){
//             $obj['taxonomy']['navigation'] = Manager::getService('CurrentUser')->getWriteNavigationTaxonomy ();
//         }
        $originalFilePointer = Manager::getService('Files')->findById($obj['originalFileId']);
        if (! $originalFilePointer instanceof \MongoGridFSFile) {
            throw new \Exception('no file found');
        }
        $obj['fileSize'] = $originalFilePointer->getSize();
        $returnArray = parent::update($obj, $options);
		
		if ($returnArray["success"]) {
            $this->_indexDam($returnArray['data']);
        }
		
		return $returnArray;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array('safe'=>true,))
    {
//         if(!isset($obj['taxonomy']['navigation']) || empty($obj['taxonomy']['navigation'])){
//             $obj['taxonomy']['navigation'] = Manager::getService('CurrentUser')->getWriteNavigationTaxonomy ();
//         }
        $originalFilePointer = Manager::getService('Files')->findById($obj['originalFileId']);
        if (! $originalFilePointer instanceof \MongoGridFSFile) {
            throw new \Exception('no file found');
        }
        $obj['fileSize'] = $originalFilePointer->getSize();
        $returnArray = parent::create($obj, $options);
		
		if ($returnArray["success"]) {
            $this->_indexDam($returnArray['data']);
        }
		
		return $returnArray;
    }

	public function getByType($typeId) {
		$filter = array(array('property' => 'typeId', 'value' => $typeId));
		
		return $this->getList($filter);
	}
}

