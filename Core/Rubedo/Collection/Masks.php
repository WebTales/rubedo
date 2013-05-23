<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IMasks, Rubedo\Services\Manager, \WebTales\MongoFilters\Filter;

/**
 * Service to handle Users
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Masks extends AbstractCollection implements IMasks
{
    protected $_model = array(
		"text" => array(
			"domain" => "string",
			"required" => true,
		),
		"site" => array(
			"domain" => "string",
			"required" => true,
		),
		"rows" => array(
			"domain" => "list",
			"required" => true,
			"items" => array(
				"domain" => "string",
				"required" => false,
			),
		),
		"blocks" => array(
			"domain" => "list",
			"required" => true,
			"items" => array(
				"domain" => "string",
				"required" => false,
			),
		),
		"mainColumnId" => array(
			"domain" => "string",
			"required" => false,
		),
	);
			
    protected $_indexes = array(
        array('keys'=>array('site'=>1)),
        array('keys'=>array('text'=>1,'site'=>1),'options'=>array('unique'=>true)),
    );
    
    /**
     * Only access to content with read access
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init(){
        parent::_init();
    
        if (! self::isUserFilterDisabled()) {
            $sites = Manager::getService('Sites')->getList();
            $sitesArray = array();
            foreach ($sites['data'] as $site){
                $sitesArray[]=(string)$site['id'];
            }            
            $filter = Filter::Factory('In');
            $filter->setName('site')->setValue($sitesArray);
            $this->_dataService->addFilter($filter);
        }
    }

	public function __construct(){
		$this->_collectionName = 'Masks';
		parent::__construct();
	}
	
	protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
			$aclServive = Manager::getService('Acl');
			
	        if (!$aclServive->hasAccess("write.ui.masks")) {
	            $obj['readOnly'] = true;
	        } else {
	            $obj['readOnly'] = false;
	        }
		}
        
        return $obj;
    }
	
	
	public function deleteBySiteId($id)
	{
		$wasFiltered = AbstractCollection::disableUserFilter();
		
		$filter = Filter::Factory('Value')->setName('site')->setValue($id);
		
		return $this->_dataService->customDelete($filter);
		AbstractCollection::disableUserFilter($wasFiltered);
	}
}
