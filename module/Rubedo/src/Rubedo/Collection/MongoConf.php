<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;
use WebTales\MongoFilters\Filter;


/**
 * Service to handle ClickStream
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class MongoConf extends AbstractCollection
{
    protected $_indexes = array(
        array(
            'keys' => array(
                'type' => 1,
            ),
            'options' => array(
                'unique' => true
            )
        )
    );

    public function __construct()
    {
        $this->_collectionName = 'MongoConf';
        parent::__construct();
    }

    public function getRubedoConf(){
        $filter=Filter::factory()->addFilter(Filter::factory("Value")->setName("type")->setValue("rubedoConfig"));
        $confRecord=$this->_dataService->findOne($filter);
        if ($confRecord&&isset($confRecord["config"])&&is_array($confRecord["config"])){
            return $confRecord["config"];
        } else {
            return null;
        }
    }

    public function setRubedoConf(array $config){
        $filter=Filter::factory()->addFilter(Filter::factory("Value")->setName("type")->setValue("rubedoConfig"));
        $confRecord=$this->_dataService->findOne($filter);
        
        if ($confRecord&&isset($confRecord["config"])&&is_array($confRecord["config"])){
            $confRecord["config"]=$config;
            return $this->_dataService->update($confRecord);
        } else {
            $newConf=[
                "type"=>"rubedoConfig",
                "config"=>$config
            ];
            return $this->_dataService->create($newConf);
        }
    }


}
