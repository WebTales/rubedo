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

namespace RubedoAPI\Rest\V1;


use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIControllerException;
use GeoIp2\Database\Reader;
use Zend\Debug\Debug;

/**
 * Class ClickstreamResource
 * @package RubedoAPI\Rest\V1
 */
class ClickstreamResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Click Stream')
            ->setDescription('Handle click stream event logging')
            ->editVerb('post', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Log event')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('fingerprint')
                            ->setRequired()
                            ->setDescription('Fingerprint')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('event')
                            ->setRequired()
                            ->setDescription('Event name')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('sessionId')
                            ->setRequired()
                            ->setDescription('Session id')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('eventArgs')
                            ->setDescription('Event Arguments')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('referrer')
                            ->setDescription('Referrer')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('referringDomain')
                            ->setDescription('Referring domain')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('screenHeight')
                            ->setDescription('Screen height')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('screenWidth')
                            ->setDescription('Screen width')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('date')
                            ->setDescription('Event date')
                    )
                    ;
            });
    }

    /**
     * Log event
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIControllerException
     */
    public function postAction($params)
    {
        //avoid crawler logs
        if (isset($_SERVER["HTTP_USER_AGENT"])&&(strpos($_SERVER["HTTP_USER_AGENT"],"PhantomJS")!==false||strpos($_SERVER["HTTP_USER_AGENT"],"Prerender")!==false)){
            return [
                "success"=>false
            ];
        }
        if (empty($params["date"])){
            $dateTime = new \DateTime();
            $params["date"]=$dateTime->format('Y-m-d\TH:i:s');
        }

        $newEvent=[
            "date"=>$params["date"],
            "fingerprint"=>$params["fingerprint"],
            "sessionId"=>$params["sessionId"],
            "event"=>$params["event"],
        ];
        if(!empty($params["referrer"])){
            $newEvent["referrer"]=$params["referrer"];
        }
        if(!empty($params["referringDomain"])){
            $newEvent["referringDomain"]=$params["referringDomain"];
        }
        if(!empty($params["screenHeight"])){
            $newEvent["screenHeight"]=$params["screenHeight"];
        }
        if(!empty($params["screenWidth"])){
            $newEvent["screenWidth"]=$params["screenWidth"];
        }
        $ua_info = \parse_user_agent();
        if(!empty($ua_info)&&is_array($ua_info) ){
            if(!empty($ua_info["screenWidth"])){
                $newEvent["browser"]=$ua_info["browser"];
            }
            if(!empty($ua_info["version"])){
                $newEvent["browserVersion"]=$ua_info["version"];
            }
            if(!empty($ua_info["platform"])){
                $newEvent["os"]=$ua_info["platform"];
            }
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        try {
            $reader = new Reader(APPLICATION_PATH . '/data/GeoLite2-City.mmdb');
            $record = $reader->city("193.248.47.139");
            $newEvent["country"]=$record->country->name;
            $newEvent["city"]=$record->city->name;
            if(isset($record->subdivisions[0])){
                $newEvent["region"]=$record->subdivisions[0]->name;
            }
            $newEvent["geoip"]=[
                "latitude"=>$record->location->latitude,
                "longitude"=>$record->location->longitude
            ];
        }
        catch(\Exception $e) {

        }
        if(!empty($params["args"])&&is_array($params["args"])){
            $newEvent=array_merge($params["args"],$newEvent);
        }
        Debug::dump($newEvent);
        die("test");

        return [
            "success"=>$logCreationResult
        ];
    }

}
