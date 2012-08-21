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
namespace Rubedo\Interfaces\Acl;

/**
 * Interface of Access Control List Implementation
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IAcl
{

    /**
     * Check if the current user has access to a given resource for a given access mode
     *
     * @param string $resource resource name
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function hasAccess($resource, $mode = 'x');

}
