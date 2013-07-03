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
 * Interface of service handling Languages
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface ILanguages extends IAbstractCollection {
	
	/**
	 * Find a language given by its Locale name
	 *
	 * @param string $name        	
	 * @return array
	 */
	public function findByLocale($name);
	
	/**
	 * Find a language given by its ISO-639-1 code (2 letters ISO code)
	 *
	 * @param string $iso        	
	 * @return array
	 */
	public function findByIso($iso);
}
