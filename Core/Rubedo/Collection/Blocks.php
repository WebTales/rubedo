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
use Rubedo\Interfaces\Collection\IBlocks;
use WebTales\MongoFilters\Filter;

/**
 * Service to handle Blocks
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks extends AbstractCollection implements IBlocks
{

    public function __construct ()
    {
        $this->_collectionName = 'Blocks';
        parent::__construct();
    }

    /**
     * Find all blocks for a given mask
     *
     * @see \Rubedo\Interfaces\Collection\IBlocks::findByMask()
     * @param string $maskId            
     * @return array
     */
    public function getListByMask ($maskId)
    {
        $filter = Filter::Factory('Value')->setName('maskId')->setValue($maskId);
        $result = $this->getList($filter);
        return $result;
    }

    /**
     * Find all blocks for a given page
     *
     * @see \Rubedo\Interfaces\Collection\IBlocks::findByPage()
     * @param string $pageId            
     * @return array
     */
    public function getListByPage ($pageId)
    {
        $filter = Filter::Factory('Value')->setName('pageId')->setValue($pageId);
        $result = $this->getList($filter);
        return $result;
    }

    /**
     * check if a block data has been modified based on a checksum
     *
     * @see \Rubedo\Interfaces\Collection\IBlocks::isModified()
     * @param array $data            
     * @return boolean
     */
    public function isModified ($data)
    {
        if (! isset($data['checksum'])) {
            return true;
        }
        return ($data['checksum'] !== $this->checksum($data));
    }

    /**
     * Compute a crc32 checksum of blockData field of a given block
     *
     * @param array $data            
     * @return number
     */
    protected function checksum ($data)
    {
        if (! isset($data['blockData'])) {
            return null;
        }
        $blockData = $data['blockData'];
        unset($blockData['checksum']);
        $serialized = serialize($blockData);
        return crc32($serialized);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $options = array())
    {
        $obj['checksum'] = $this->checksum($obj);
        parent::create($obj, $options);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array())
    {
        $obj['checksum'] = $this->checksum($obj);
        parent::update($obj, $options);
    }

    /**
     * extract data part of a block object
     *
     * @see \Rubedo\Interfaces\Collection\IBlocks::getBlockData()
     * @param array $data            
     * @return array
     */
    public function getBlockData ($data)
    {
        $result = $data['blockData'];
        $result['checksum'] = $data["checksum"];
        return $result;
    }
}
