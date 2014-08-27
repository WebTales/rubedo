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
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\AbstractRessource;
use Zend\Json\Json;


/**
 * Class SearchRessource
 * @package RubedoAPI\Rest\V1
 */
class SearchRessource extends AbstractRessource
{
    protected $searchOption;

    public function __construct()
    {
        parent::__construct();
        $this->searchOption = 'all';
        $this
            ->definition
            ->setName('Search')
            ->setDescription('Search with ElasticSearch')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a list of media using Elastic Search')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('sort')
                            ->setDescription('Sort parameter, must be \'asc\' or \'desc\'')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('orderBy')
                            ->setDescription('OrderBy parameter')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('query')
                            ->setDescription('query parameter')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('predefinedFacets')
                            ->setDescription('Json array facets')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('siteId')
                            ->setDescription('Id of the site')
                            ->setFilter('\\MongoId')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('constrainToSite')
                            ->setDescription('Property to constrain to the site given with siteId')
                            ->setFilter('boolean')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('start')
                            ->setDescription('Item\'s index number to start')
                            ->setFilter('int')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('limit')
                            ->setDescription('How much contents to return')
                            ->setFilter('int')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('results')
                            ->setDescription('List of result return by the research')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('count')
                            ->setDescription('Number of results return by the research')
                    );
            });
    }

    public function getAction($queryParams)
    {
        $params = $this->initParams($queryParams);
        $facetsToHide = $this->setFacetsParams($params, $queryParams);

        \Rubedo\Elastic\DataSearch::setIsFrontEnd(true);
        $query = Manager::getService('ElasticDataSearch');
        $query->init();

        $results = $query->search($params, $this->searchOption);
        $results['facetsToHide'] = $facetsToHide;

        $this->injectDataInResults($results);

        return [
            'success' => true,
            'results' => $results,
            'count' => $results['total']
        ];

    }

    protected function initParams($queryParams)
    {
        $params = array(
            'limit' => isset($queryParams['limit']) ? $queryParams['limit'] : 25,
            'start' => isset($queryParams['start']) ? $queryParams['start'] : 0,
        );

        if (isset($queryParams['orderBy'])) {
            $params['orderBy'] = $queryParams['orderBy'];
        }

        if (isset($queryParams['sort'])) {
            $params['orderByDirection'] = $queryParams['sort'];
        }

        if (isset($queryParams['constrainToSite']) && $queryParams['constrainToSite'] && isset($queryParams['siteId'])) {
            $params['navigation'][] = $queryParams['siteId'];
        }

        return $params;

    }

    protected function setFacetsParams(&$params, $queryParams)
    {
        $facetsToHide = array();
        if (isset($queryParams['predefinedFacets'])) {
            $predefParamsArray = Json::decode(
                $queryParams['predefinedFacets'],
                Json::TYPE_ARRAY);
            if (is_array($predefParamsArray)) {
                foreach ($predefParamsArray as $key => $value) {
                    if ($key != 'query') {
                        $params[$key][] = $value;
                    } else {
                        $params[$key] = $value;
                    }
                    $facetsToHide[] = $value;
                }
            }
        }
        return $facetsToHide;
    }

    protected function injectDataInResults(&$results)
    {

    }
}