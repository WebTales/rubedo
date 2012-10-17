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
namespace Rubedo\Interfaces\Router;

/**
 * Front Office URL service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
Interface IUrl {

	/**
	 * Return page id based on request URL
	 *
	 * @param string $url requested URL
	 * @return string|int 
	 */
	public function getPageId($url);

}
