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
namespace Rubedo\Update;

use WebTales\MongoFilters\Filter;
use Rubedo\Services\Manager;

/**
 * Methods for update tool
 *
 * @author jbourdin
 *        
 */
class Update010202 extends Update {
	protected static $toVersion = '1.3.0';
	
	/**
	 * do the upgrade
	 *
	 * @return boolean
	 */
	public static function upgrade() {
	    static::defaultCtypeCode();
        return true;
    } 
    
    /**
     * Set not filed dam items in the directory 'not filed'
     *
     * @return boolean
     */
    public static function defaultCtypeCode()
    {
        $data = array(
            '$set' => array(
                'code' => 'article'
            )
        );
        $updateCond = Filter::factory('Value')->setName('defaultId')->setValue('51a60bb0c1c3dac60700000e');
        Manager::getService('ContentTypes')->customUpdate($data, $updateCond);
        
        $data = array(
            '$set' => array(
                'code' => 'event'
            )
        );
        $updateCond = Filter::factory('Value')->setName('defaultId')->setValue('51a60bbdc1c3da9a0a000009');
        Manager::getService('ContentTypes')->customUpdate($data, $updateCond);
        
        $data = array(
            '$set' => array(
                'code' => 'news'
            )
        );
        $updateCond = Filter::factory('Value')->setName('defaultId')->setValue('51a60bcdc1c3dadc08000012');
        Manager::getService('ContentTypes')->customUpdate($data, $updateCond);
        return true;
    }
}