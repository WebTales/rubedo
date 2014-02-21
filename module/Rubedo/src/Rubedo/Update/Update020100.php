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

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Json\Json;
/**
 * Methods
 * for
 * update
 * tool
 *
 * @author adobre
 *
 */
class Update020100 extends Update
{

    protected static $toVersion = '2.2.0';


    public static function doImportCountries ()
    {
        $success=true;
        $file = APPLICATION_PATH . '/data/countries.json';
        $json = file_get_contents($file, 'r');
        $countries=Json::decode($json, Json::TYPE_ARRAY);
        $service = Manager::getService('Countries');
        foreach ($countries as $country){
            $upsertFilter = Filter::factory('Value')->setName('alpha-3')->setValue($country["alpha-3"]);
            $upserted=$service->create($country, array(
                'upsert' => $upsertFilter
            ));
            $success= $success&&$upserted['success'];
        }
        return $success;
    }


    /**
     * do
     * the
     * upgrade
     *
     * @return boolean
     */
    public static function upgrade ()
    {

        // import countries
        static::doImportCountries();

        return true;
    }


}