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
     * @param array $block bloc options (type, filter params...)
     * @return array block data to be rendered
     */
    public function getBlockData($block);

}
