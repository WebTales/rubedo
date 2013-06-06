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
 * Interface of service handling Pages
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IPages extends IAbstractCollection{
	
	
/**
 * find a page by name and siteID
 * 
 * @param string $name Name of the page
 * @param string $siteId Site Id
 * 
 * @return array
 * 
 */
		public function findByNameAndSite($name,$siteId);
		public function matchSegment($urlSegment,$parentId,$siteId);
		public function getListByMaskId($maskId);
		public function isMaskUsed($maskId);
		public function deleteBySiteId($id);
		public function clearOrphanPages();
		public function countOrphanPages();
		public function propagateWorkspace ($parentId, $workspaceId, $siteId = null);
}
