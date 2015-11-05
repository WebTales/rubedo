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

use Rubedo\Interfaces\Collection\IClickStream;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Debug\Debug;
use Zend\Json\Json;

/**
 * Service to handle ClickStream
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class ClickStream extends AbstractCollection implements IClickStream
{

    public function __construct()
    {
        $this->_collectionName = 'ClickStream';
        parent::__construct();
    }

    protected $_indexes = array(
        array(
            'keys' => array(
                'fingerprint' => 1,
                'event' => 1,
                'sessionId' => 1,
            )
        ),array(
            'keys' => array(
                'fingerprint' => 1,
                'event' => 1,
            )
        )
    );


    public function log($obj)
    {
        if (!isset($obj["fingerprint"],$obj["event"],$obj["sessionId"],$obj["timestamp"])){
            return false;
        }
        $this->_dataService->directCreate($obj);
        return true;
    }

    public function getClusterMatrix(){
        $filter=Filter::factory();
        $allTaxosArray=Manager::getService("Taxonomy")->getList($filter);
        $taxoTermsService=Manager::getService("TaxonomyTerms");
        $projectorArray=[];
        foreach($allTaxosArray["data"] as $theTaxonomy){
            $TaxoPonderation=isset($theTaxonomy["ponderationFactor"])&&is_numeric($theTaxonomy["ponderationFactor"]) ? $theTaxonomy["ponderationFactor"] : 1;
            $termsFilter=Filter::factory();
            $termsFilter->addFilter(Filter::factory("Value")->setName("vocabularyId")->setValue($theTaxonomy["id"]));
            $termsTree=$taxoTermsService->readTree($termsFilter);
            $this->addTermsToProjector($termsTree,$projectorArray,1,$TaxoPonderation);

        }
        $map=new \MongoCode("function() { emit(this.fingerprint,this.eventArgs.allContentTerms); }");
        $reduce=new \MongoCode("function(k,vals) {
            var projectorArray=".Json::encode($projectorArray).";
            var vector={};
            for (var i in vals) {
                for (var j in vals[i]){
                    var term=vals[i][j];
                    if (vector[term]){
                        vector[term]=vector[term]+projectorArray[term]['ponderation'];
                    } else {
                        vector[term]=projectorArray[term]['ponderation'];
                    }
                    var ancestors=projectorArray[term]['ancestors'];
                    for (var s in ancestors){
                        var subTerm=ancestors[s];
                        if (vector[subTerm]){
                            vector[subTerm]=vector[subTerm]+projectorArray[subTerm]['ponderation'];
                        } else {
                            vector[subTerm]=projectorArray[subTerm]['ponderation'];
                        }
                    }
                }
            }
            return vector;
         }
        ");
        $mrParams = array(
            "mapreduce" => "ClickStream", // collection
            "query" => array("event" => "contentDetailView"), // query
            "map" => $map, // map
            "reduce" => $reduce, // reduce
            "out" => array("replace" => "UserVectors") // out
        );
        $this->_dataService->command($mrParams);
        return([
            "status"=>"built",
            "collection"=>"UserVectors"
        ]);


    }

    private function addTermsToProjector ($terms,&$projectorArray,$level,$factor,$ancestors=array()){
        foreach($terms as $term){
            $projectorArray[$term["id"]]=array(
                "ponderation"=>$level*$factor,
                "ancestors"=>$ancestors
            );
            if (isset($term["children"])&&is_array($term["children"])){
                $newAncestors=$ancestors;
                $newAncestors[]=$term["id"];
                $this->addTermsToProjector($term["children"],$projectorArray,$level+1,$factor,$newAncestors);
            }
        }
    }
}
