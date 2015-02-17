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

namespace RubedoAPI\Rest\V1\Ecommerce\Products;

use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\SearchResource as GlobalSearch;

/**
 * Class SearchResource
 * @package RubedoAPI\Rest\V1\Ecommerce\Products
 */
class SearchResource extends GlobalSearch
{
    public function __construct()
    {
        parent::__construct();
        $this->searchOption = 'content';
        $this
            ->definition
            ->setName('Products')
            ->setDescription('Deal with products')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a list of products using Elastic Search');
            });
    }

    /**
    * Get action
    * @param $queryParams
    * @return array
    */
    public function getAction($queryParams)
    {
        $params = $this->initParams($queryParams);

        $query = $this->getElasticDataSearchService();
        $query::setIsFrontEnd(true);
        $query->init();
        //add product param here
        $results = $query->search($params, $this->searchOption);

        $this->injectDataInResults($results, $queryParams);

        return [
            'success' => true,
            'results' => $results,
            'count' => $results['total']
        ];

    }

}