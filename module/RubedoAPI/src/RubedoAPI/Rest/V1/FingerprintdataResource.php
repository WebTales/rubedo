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
use RubedoAPI\Exceptions\APIEntityException;
use WebTales\MongoFilters\Filter;

/**
 * Class FingerprintdataResource
 * @package RubedoAPI\Rest\V1
 */
class FingerprintdataResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Fingerprint Data')
            ->setDescription('Handle fingerprint data persistence')
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
                            ->setKey('property')
                            ->setRequired()
                            ->setDescription('Property')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('operator')
                            ->setRequired()
                            ->setDescription('Operator')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('value')
                            ->setDescription('Value')
                    );
            })
            ->editVerb('get', function (VerbDefinitionEntity &$verbDef) {
                $verbDef->setDescription('Get data for specific fingerprint')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('fingerprint')
                            ->setRequired()
                            ->setDescription('Fingerprint')
                            ->setFilter('string')
                    )->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('data')
                            ->setDescription('Data')
                    );
            });

    }

    /**
     * Post action
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIControllerException
     */
    public function postAction($params)
    {
        if (isset($params["value"])){
            $logCreationResult=Manager::getService("FingerprintData")->log($params["fingerprint"],$params["property"],$params["operator"],$params["value"]);
        } else {
            $logCreationResult=Manager::getService("FingerprintData")->log($params["fingerprint"],$params["property"],$params["operator"]);
        }
        return [
            "success"=>$logCreationResult
        ];
    }

    /**
     * Get action
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function getAction($params)
    {
        $filter=Filter::factory();
        $filter->addFilter(Filter::factory("Value")->setName("fingerprint")->setValue($params["fingerprint"]));
        $entity=Manager::getService("FingerprintData")->findOne($filter);
        if (!$entity){
            throw new APIEntityException('No data found for this fingerprint', 404);
        }
        return [
            "success"=>true,
            "data"=>$entity
        ];
    }

}