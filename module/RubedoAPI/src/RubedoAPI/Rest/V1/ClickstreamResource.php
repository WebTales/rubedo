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
                            ->setKey('userAgent')
                            ->setDescription('User agent')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('referrer')
                            ->setDescription('Referrer')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('os')
                            ->setDescription('OS')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('url')
                            ->setDescription('URL')
                    );
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
        $currentTime = $this->getCurrentTimeService()->getCurrentTime();
        $newEvent=[
            "date"=>new \MongoDate($currentTime),
            "event"=>$params["event"],
            "eventArgs"=>isset ($params["eventArgs"]) ? $params["eventArgs"] : [ ],
            "lang"=>$params['lang']->getLocale()
        ];
        if (isset($params["referrer"])){
            $newEvent["referrer"]=$params["referrer"];
        }
        if (isset($params["url"])){
            $newEvent["url"]=$params["url"];
        }
        $currentUser=$this->getCurrentUserAPIService()->getCurrentUser();
        $userId=$currentUser ? $currentUser["id"] : null;
        $userAgent=isset($params["userAgent"]) ? $params["userAgent"] : null;
        $os=isset($params["os"]) ? $params["os"] : null;
        $logCreationResult=Manager::getService("ClickStream")->log($params["fingerprint"],$params["sessionId"],$newEvent,$userId,$userAgent,$os);
        return [
            "success"=>$logCreationResult
        ];
    }

}