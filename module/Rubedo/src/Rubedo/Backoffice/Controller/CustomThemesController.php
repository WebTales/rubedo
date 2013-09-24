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
namespace Rubedo\Backoffice\Controller;

use Rubedo\Services\Manager;
use Zend\Json\Json;

/**
 * Controller providing CRUD API for the mailing lists JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class CustomThemesController extends DataAccessController
{
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'get-color-palette',
        'get-color-palette-bo'
    );
    
    public function __construct ()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('CustomThemes');
    }
    
    public function getColorPaletteAction ()
    {
        $curl = curl_init();
        $offset=rand(1, 1000);
        curl_setopt($curl,CURLOPT_URL,"http://www.colourlovers.com/api/palettes/top?format=json&numResults=1&resultOffset=".$offset);
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        $json = curl_exec($curl);
        curl_close($curl);
        return $this->_returnJson(Json::decode($json, Json::TYPE_ARRAY));
    }
    
    public function getColorPaletteBoAction ()
    {

        $values=$this->params()->fromPost('values');
        $curl = curl_init();
        $offset=rand(1, 1000);
        curl_setopt($curl,CURLOPT_URL,"http://www.colourlovers.com/api/palettes/top?format=json&numResults=1&resultOffset=".$offset."&hueOption=".$values);
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        $json = curl_exec($curl);
        curl_close($curl);
        return $this->_returnJson(Json::decode($json, Json::TYPE_ARRAY));
    }
}