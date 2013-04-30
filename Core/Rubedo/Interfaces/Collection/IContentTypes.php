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
 * Interface of service handling ContentTypes
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IContentTypes extends IAbstractCollection{
    
    /**
     * Return the list of ID of contentTypes the current user can read.
     * 
     * @return array
     */
	public function getReadableContentTypes();
	
	/**
	 * Return the list of ID of contentTypes which implements a location field
	 * 
	 * @return array
	 */
	public function getGeolocatedContentTypes();
	
	/**
	 * Push the content type to Elastic Search
	 *
	 * @param array $obj
	 */
	public function indexContentType ($obj);
	
	/**
	 * Remove the content type from Indexed Search
	 *
	 * @param array $obj
	 */
	public function unIndexContentType ($obj);
}
