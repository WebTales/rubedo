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
       static::importLanguages();
       static ::siteStrategy();
        return true;
    }

    /**
     * Set not filed dam items in the directory 'not filed'
     *
     * @return boolean
     */
    public static function siteStrategy()
    {
        $data = array(
            '$set' => array(
                'locStrategy' => 'onlyOne'
            )
        );
        $updateCond = Filter::factory('OperatorToValue')->setName('locStrategy')
        ->setOperator('$exists')
        ->setValue(false);
        $options = array(
            'multiple' => true
        );
        Manager::getService('Sites')->customUpdate($data, $updateCond, $options);
        return true;
    }
    
    public static function importLanguages(){
    	$tsvFile = APPLICATION_PATH.'/../data/ISO-639-2_utf-8.txt';
    	$file = fopen($tsvFile, 'r');
    	$service = Manager::getService('Languages');
    	while($line = fgetcsv($file,null,'|')){
    		if(empty($line[2])){
    			continue;
    		}
    		$lang = array();
    		$lang['iso2']=$line[2];
    		$lang['locale']=$line[2];
    		$lang['iso3']=$line[0];
    		$lang['label']=$line[3];
    		$lang['labelFr']=$line[4];
    	
    		$upsertFilter = Filter::factory('Value')->setName('locale')->setValue($lang['locale']);
    		$service->create($lang,array('upsert'=>$upsertFilter));
    	}
    	return true;
    }
 
}