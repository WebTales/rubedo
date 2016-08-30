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

/**
 * Class ClickstreamResource
 * @package RubedoAPI\Rest\V1
 */
class EventfacetResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Click Stream event facet')
            ->setDescription('Get event facet')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get event facet')
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('data')
                            ->setRequired()
                            ->setDescription('Data')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('event')
                            ->setRequired()
                            ->setDescription('Event')
                            ->setFilter('string')
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
                            ->setKey('facet')
                            ->setRequired()
                            ->setDescription('Facet')
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
        $data=Manager::getService("ElasticClickStream")->getEventFacet($params["startDate"],$params["endDate"],$params["facet"],$params["event"]);
        return [
            "success"=>true,
            "data"=>$data
        ];
    }

}
