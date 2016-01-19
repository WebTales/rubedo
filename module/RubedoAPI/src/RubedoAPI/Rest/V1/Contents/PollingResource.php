<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2016, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2016 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Rest\V1\Contents;

use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Rest\V1\AbstractResource;

/**
 * Class SearchResource
 * @package RubedoAPI\Rest\V1\Contents
 */
class PollingResource extends AbstractResource
{
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Get to contents
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function getAction($params)
    {
        $contents = $this->getContentsCollection()->getList();

        return [
            'success' => true,
            'contents' => $contents["data"],
            'count' => $contents["count"]
        ];
    }

    /**
     * Define the resource
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('Contents polling')
            ->setDescription('Allow to poll contents')
            ->editVerb('get', function (VerbDefinitionEntity &$definition) {
                $this->defineGet($definition);
            });
    }

    /**
     * Define get action
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function defineGet(VerbDefinitionEntity &$definition)
    {
        $definition
            ->setDescription('Get a list of contents')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('siteId')
                    ->setDescription('Id of the site')
                    ->setFilter('\\MongoId')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('fields')
                    ->setDescription('Mask of fields')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('limit')
                    ->setDescription('Number of contents returned')
                    ->setFilter('int')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('count')
                    ->setDescription('Number of all contents')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('success')
                    ->setDescription('Status of the query')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('contents')
                    ->setDescription('Contents returned by query')
            );
    }

}