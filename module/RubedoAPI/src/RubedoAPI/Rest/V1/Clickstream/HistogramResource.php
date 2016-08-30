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

namespace RubedoAPI\Rest\V1\Clickstream;


use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\AbstractResource;
use Zend\Debug\Debug;
use Zend\Json\Json;

/**
 * Class ClickstreamResource
 * @package RubedoAPI\Rest\V1
 */
class HistogramResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Click Stream histogram')
            ->setDescription('Get event histogram')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Log event')
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('data')
                            ->setRequired()
                            ->setDescription('Data')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('filters')
                            ->setRequired()
                            ->setDescription('Filters')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('startDate')
                            ->setRequired()
                            ->setDescription('Start date (YYYY-MM-DD)')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('endDate')
                            ->setRequired()
                            ->setDescription('Start date (YYYY-MM-DD)')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('granularity')
                            ->setRequired()
                            ->setDescription('Granularity : "hour" or "day"')
                            ->setFilter('string')
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
    public function getAction($params)
    {
        $filters=Json::decode($params["filters"],JSON::TYPE_ARRAY);
        $data=Manager::getService("ElasticClickStream")->getDateHistogramAgg($params["startDate"],$params["endDate"],$params["granularity"],$filters);
        return [
            "success"=>true,
            "data"=>$data
        ];
    }

}
