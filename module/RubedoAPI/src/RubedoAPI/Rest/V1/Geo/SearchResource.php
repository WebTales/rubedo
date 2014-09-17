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

namespace RubedoAPI\Rest\V1\Geo;

use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Rest\V1\SearchResource as GlobalSearch;

/**
 * Class SearchResource
 * @package RubedoAPI\Rest\V1\Geo
 */
class SearchResource extends GlobalSearch
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->searchOption = 'geo';
        $this->searchParamsArray = array('orderby', 'orderbyDirection','query','objectType','type','damType','userType','author',
            'userName','lastUpdateTime','start','limit',"inflat","inflon","suplat","suplon");
        $this
            ->definition
            ->setName('Geo')
            ->setDescription('Deal with geolocated data')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a list of geolocated items using Elastic Search')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('inflat')
                            ->setDescription('Min latitude')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('inflon')
                            ->setDescription('Min longitude')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('suplat')
                            ->setDescription('Max latitude')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('suplon')
                            ->setDescription('Max longitude')
                    );
            });
    }
}