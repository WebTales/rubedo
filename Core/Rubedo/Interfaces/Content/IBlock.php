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

namespace Rubedo\Interfaces\Content;

/**
 * Block Content Service
 *
 * Get current user and user informations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IBlock
{
    /**
     * Return the data associated to a block given by config array
     * @param array $blockConfig bloc options (type, filter params...)
     * @param array $page parent page info
     * @param Zend_Controller_Action $parentController
     * @return array block data to be rendered
     */
    public function getBlockData($blockConfig, $page, $parentController);

}
