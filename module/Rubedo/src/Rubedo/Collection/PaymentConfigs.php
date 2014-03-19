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
namespace Rubedo\Collection;
use WebTales\MongoFilters\Filter;
use Rubedo\Services\Manager;

/**
 * Service to handle Payment Configs
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class PaymentConfigs extends AbstractCollection
{
    public function __construct()
    {
        $this->_collectionName = 'PaymentConfigs';
        parent::__construct();
    }

    /**
     * Gets the config for a specific payment means, makes sure payment means is installed, autocreates an inactive one if non existent
     *
     * @param $pmName
     * @return array
     */

    public function getConfigForPM ($pmName){
        $rConfig = Manager::getService('config');
        $installedPM=$rConfig['paymentMeans'];
        if (!isset($installedPM[$pmName])){
            return(array(
                'success'=>false,
                'msg'=>"Payment means not installed"
            ));
        }
        $filter = Filter::factory('Value');
        $filter->setName('paymentMeans')->setValue($pmName);
        $configForPM=$this->findOne($filter);
        if (!$configForPM){
            $configForPM=$this->create(array(
                "paymentMeans"=>$pmName,
                "active"=>false,
                "displayName"=>$installedPM[$pmName]["name"],
                "logo"=>null,
                "nativePMConfig"=>array()
            ));
            if (!$configForPM['success']){
                return(array(
                    'success'=>false,
                    'msg'=>"Failed to autocreate config"
                ));
            }
            $configForPM=$configForPM['data'];
        }
        return(array(
            'success'=>true,
            'data'=>$configForPM
        ));
    }

    public function getActivePMConfigs(){
        $rConfig = Manager::getService('config');
        $installedPM=array_keys($rConfig['paymentMeans']);
        $filters = Filter::factory()->addFilter(Filter::factory('Value')->setName('active')->setValue(true))
            ->addFilter(Filter::factory('In')->setName('paymentMeans')->setValue($installedPM));
        $result=$this->getList($filters);
        return $result;
    }

}