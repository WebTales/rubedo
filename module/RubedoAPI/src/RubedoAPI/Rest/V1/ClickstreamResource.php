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
            "referrer"=>!empty($params["referrer"])? $params["referrer"] : null,
            "referringDomain"=>!empty($params["referringDomain"])? $params["referringDomain"] : null,
            "screenHeight"=>!empty($params["screenHeight"])? $params["screenHeight"] : null,
            "screenWidth"=>!empty($params["screenWidth"])? $params["screenWidth"] : null,
            "browser"=>null,
            "browserVersion"=>null,
            "os"=>null,
        ];

        if(!empty($params["args"])&&is_array($params["args"])){
            $newEvent=array_merge($params["args"],$newEvent);
        }
        $ua_info = \parse_user_agent();
        if(!empty($ua_info)&&is_array($ua_info) ){
            $newEvent["browser"]=!empty($ua_info["browser"])? $ua_info["browser"] : null;
            $newEvent["browserVersion"]=!empty($ua_info["version"])? $ua_info["version"] : null;
            $newEvent["os"]=!empty($ua_info["platform"])? $ua_info["platform"] : null;
        }
        Debug::dump($newEvent);
        die("test");
        return [
            "success"=>$logCreationResult
        ];
    }

}
