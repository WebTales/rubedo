<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Interfaces\Security;

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
     * @return boolean
     */
    public function hasAccess($resource);
	
	/**
	 * For a given list of ressource, build an array of authorized ressources
	 * @param array $ressourceArray array of ressources
	 * @return array the array of boolean with ressource as key name
	 */
	public function accessList(array $ressourceArray);

}
