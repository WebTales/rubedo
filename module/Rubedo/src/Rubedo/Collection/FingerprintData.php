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

use Rubedo\Interfaces\Collection\IFingerprintData;
use WebTales\MongoFilters\Filter;

/**
 * Service to handle ClickStream
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class FingerprintData extends AbstractCollection implements IFingerprintData
{

    public function __construct()
    {
        $this->_collectionName = 'FingerprintData';
        parent::__construct();
    }

    protected $_indexes = array(
        array(
            'keys' => array(
                'fingerprint' => 1,
            )
        )
    );


    public function log($fingerprint, $property, $operator, $value)
    {
        if (!isset($fingerprint, $property, $operator)||!in_array($operator,["inc","dec","set"])){
            return false;
        }
        if (!isset($value)){
            if ($operator=="inc"){
                $value=1;
            } elseif ($operator=="dec"){
                $value=-1;
            } elseif ($operator=="set"){
                $value=null;
            }
        }

        $mongoOp='$set';
        if ($operator=="inc"||$operator=="dec"){
            $mongoOp='$inc';
            $value=(int) $value;
        }

        if ($operator=="dec"&&$value>0){
            $value = -$value;
        }


        $filter=Filter::factory();
        $filter->addFilter(Filter::factory("Value")->setName("fingerprint")->setValue($fingerprint));
        $updateObj=[
            $mongoOp=>[
                $property=>$value
            ]
        ];
        $this->_dataService->customUpdate($updateObj,$filter,array("upsert"=>true,"w"=>0));
        return true;
    }
}
