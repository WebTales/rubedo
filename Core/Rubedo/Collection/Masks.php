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

use Rubedo\Interfaces\Collection\IMasks, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

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
            "required" => true
        ),
        "site" => array(
            "domain" => "string",
            "required" => true
        ),
        "rows" => array(
            "domain" => "list",
            "required" => true,
            "items" => array(
                "domain" => "string",
                "required" => false
            )
        ),
        "blocks" => array(
            "domain" => "list",
            "required" => true,
            "items" => array(
                "domain" => "string",
                "required" => false
            )
        ),
        "mainColumnId" => array(
            "domain" => "string",
            "required" => false
        )
    );

    protected $_indexes = array(
        array(
            'keys' => array(
                'site' => 1
            )
        ),
        array(
            'keys' => array(
                'text' => 1,
                'site' => 1
            ),
            'options' => array(
                'unique' => true
            )
        )
    );

    /**
     * Only access to content with read access
     * 
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init ()
    {
        parent::_init();
        
        if (! self::isUserFilterDisabled()) {
            $sites = Manager::getService('Sites')->getList();
            $sitesArray = array();
            foreach ($sites['data'] as $site) {
                $sitesArray[] = (string) $site['id'];
            }
            $filter = Filter::factory('In');
            $filter->setName('site')->setValue($sitesArray);
            $this->_dataService->addFilter($filter);
        }
    }

    public function __construct ()
    {
        $this->_collectionName = 'Masks';
        parent::__construct();
    }

    protected function _addReadableProperty ($obj)
    {
        $obj = $this->addBlocks($obj);
        if (! self::isUserFilterDisabled()) {
            $aclServive = Manager::getService('Acl');
            
            if (! $aclServive->hasAccess("write.ui.masks")) {
                $obj['readOnly'] = true;
            } else {
                $obj['readOnly'] = false;
            }
        }
        
        return $obj;
    }

    public function deleteBySiteId ($id)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        
        $filter = Filter::factory('Value')->setName('site')->setValue($id);
        
        return $this->_dataService->customDelete($filter);
        AbstractCollection::disableUserFilter($wasFiltered);
    }

    protected function _initContent ($obj)
    {
        if (isset($obj['id'])) {
            $obj = $this->writeBlocks($obj);
        }
        return $obj;
    }

    /**
     * Save the blocks of the given page
     *
     * Delete the no longer used blocks.
     *
     * @param array $obj            
     * @return array
     */
    protected function writeBlocks ($obj)
    {
        $blocksService = Manager::getService('Blocks');
        $arrayOfBlocksId = $blocksService->getIdListByMask($obj['id']);
        $blocks = $obj['blocks'];
        foreach ($blocks as $block) {
            $blocksService->upsertFromData($block, $obj['id'], 'mask');
            if (isset($arrayOfBlocksId[$block['id']])) {
                unset($arrayOfBlocksId[$block['id']]);
            }
        }
        if (count($arrayOfBlocksId) > 0) {
            $blocksService->deletedByArrayOfId(array_keys($arrayOfBlocksId));
        }
        
        $obj['blocks'] = array();
        return $obj;
    }

    /**
     * Add blocks from blocks collection to the given page
     *
     * @param array $obj            
     * @return array
     */
    protected function addBlocks ($obj)
    {
        $blocksTemp = array();
        $blocksService = Manager::getService('Blocks');
        $blockList = $blocksService->getListByMask($obj['id']);
        foreach ($blockList['data'] as $block) {
            $temp = $blocksService->getBlockData($block);
            $temp['canEdit'] = 1;
            $blocksTemp[] = $temp;
        }
        if (count($blocksTemp) > 0) {
            $obj['blocks'] = $blocksTemp;
        }
        return $obj;
    }

    public function create (array $obj, $options = array())
    {
        $obj = $this->_initContent($obj);
        $result = parent::create($obj, $options);
        $result['data'] = $this->addBlocks($result['data']);
        return $result;
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array())
    {
        $obj = $this->_initContent($obj);
        
        $returnValue = parent::update($obj, $options);
        if ($returnValue['success']) {
            $returnValue['data'] = $this->addBlocks($returnValue['data']);
        }
        return $returnValue;
    }
}
