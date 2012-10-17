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

namespace Rubedo\Content;

Use Rubedo\Interfaces\Content\IBlock;
/**
 * Block Content Service
 *
 * Get current user and user informations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Block implements IBlock
{
    /**
     * Return the data associated to a block given by config array
     * @param array $blockConfig
	 * @param Zend_Controller_Action $parentController
     * @return array
     */
    public function getBlockData($blockConfig,$parentController){
    		$helper= 'helper'.$blockConfig['Module'];
			$output = $blockConfig['Output'];
			$input = $blockConfig['Input'];
			return array($output => $parentController->getProtectedHelper()->$helper($input));
    }

    
}
