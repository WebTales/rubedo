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
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling Blocks
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IBlocks extends IAbstractCollection{
    
    /**
     * Find all blocks for a given page
     * 
     * @param string $pageId
     * @return array
     */
    public function getListByPage($pageId);
    
    /**
     * Find all blocks for a given mask
     *
     * @param string $maskId
     * @return array
     */
    public function getListByMask($maskId);
    
    /**
     * check if a block data has been modified based on a checksum
     * @param array $data
     * @return boolean
     */
    public function isModified($data);
    
    /**
     * extract data part of a block object
     * @param array $data
     * @return array
     */
    public function getBlockData($data);
	
}
