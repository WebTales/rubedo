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
 * Interface of service handling Contents
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IContents extends IAbstractCollection{
	public function unsetTerms ($vocId, $termId);
	public function getByType ($typeId);
	public function clearOrphanContents ();
	public function countOrphanContents ();
	public function getListByTypeId ($typeId);
	public function isTypeUsed ($typeId);
	public static function getIsFrontEnd ();
	public static function setIsFrontEnd ($_isFrontEnd);
	/**
	 * Return a list of ordered objects
	 *
	 * @param array $filters
	 * @param array $sort
	 * @param string $start
	 * @param string $limit
	 * @param bool $live
	 *
	 * @return array Return the contents list
	 */
	public function getOrderedList($filters = null, $sort = null, $start = null, $limit = null, $live = true);
	
}
