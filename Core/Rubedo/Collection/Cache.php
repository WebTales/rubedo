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

use Rubedo\Interfaces\Collection\ICache;
use Rubedo\Services\Manager;

/**
 * Service to handle cached contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Cache extends AbstractCollection implements ICache
{

    protected $_model = array(
        'data' => array(
            'domain' => 'string',
            'required' => true,
        ),
        'cacheId' => array(
            'domain' => 'string',
            'required' => true
        ),
        'expire' => array(
            'domain' => 'tstamp',
            'required' => false
        ),
        'tags' => array(
            'domain' => 'list',
            'required' => false,
            'subConfig' => array(
                'domain' => 'string',
                'required' => false
            )
        )
    );

    public function __construct ()
    {
        $this->_collectionName = 'Cache';
        parent::__construct();
    }

    public function findByCacheId ($cacheId, $time = null)
    {
        $cond = array();
        $cond['cacheId'] = $cacheId;
        if ($time) {
            $cond['expire'] = array(
                '$gt' => $time
            );
        }
        return $this->_dataService->findOne($cond);
    }

    /**
     * Update object or insert if not present base on the CacheId field
     *
     * @param array $obj
     *            inserted data
     * @param string $cacheId
     *            string parameter of the cache entry
     * @return bool
     */
    public function upsertByCacheId ($obj, $cacheId)
    {
        $obj = $this->_filterInputData($obj);
        $options = array();
        $options['safe'] = true;
        $options['upsert'] = true;
        
        $updateCond = array(
            'cacheId' => $cacheId
        );
        
        $result = $this->_dataService->customUpdate($obj, $updateCond, $options);
        if ($result['success']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove expired cache items
     *
     * @return boolean
     */
    public function deledExpired ()
    {
        $options = array();
        $options['safe'] = true;
        $updateCond["expire"] = array(
            '$lt' => Manager::getService('CurrentTime')->getCurrentTime()
        );
        $result = $this->_dataService->customDelete($updateCond, $options);
        if ($result['ok']) {
            return true;
        } else {
            return false;
        }
    }
	public function deleteByCacheId($id){
		 $updateCond = array(
            'cacheId' => $id
        );
        $options = array();
        $options['safe'] = true;
		$result = $this->_dataService->customDelete($updateCond, $options);
        if ($result['success']) {
            return true;
        } else {
            return false;
        }
	}
	
	
    
}
