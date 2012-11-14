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
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling Contents
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface INestedContents {
	
	/**
     * Do a find request on nested contents of a given content
     *
	 * @param string $parentContentId parent id of nested contents
	 * @param array $filters filter the list with mongo syntax
	 * @param array $sort sort the list with mongo syntax
     * @return array
     */
    public function getList($parentContentId,$filters = null, $sort = null);
	
}
