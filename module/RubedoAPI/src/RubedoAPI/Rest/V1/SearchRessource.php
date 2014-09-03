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
                            ->setKey('orderbyDirection')
                            ->setDescription('Sort parameter, must be \'asc\' or \'desc\'')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('orderby')
                            ->setDescription('Orderby parameter')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('type')
                            ->setDescription('Content Type array')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('damType')
                            ->setDescription('Dam Type array')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('objectType')
                            ->setDescription('object Type array')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('userType')
                            ->setDescription('user Type array')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('author')
                            ->setDescription('author')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('userName')
                            ->setDescription('userName')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('taxonomies')
                            ->setDescription('Taxonomies Array')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('lastUpdateTime')
                            ->setDescription('lastUpdateTime')
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
                            ->setKey('displayedFacets')
                            ->setDescription('Json array displayed facets')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('displayMode')
                            ->setDescription('Display Mode')
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

        \Rubedo\Elastic\DataSearch::setIsFrontEnd(true);
        $query = Manager::getService('ElasticDataSearch');
        $query->init();

        $results = $query->search($params, $this->searchOption);

        $this->injectDataInResults($results);

        return [
            'success' => true,
            'results' => $results,
            'count' => $results['total']
        ];

    }

    protected function initParams($queryParams)
    {
        $blockConfigArray = array('displayMode', 'displayedFacets');
        $searchParamsArray = array('orderby', 'orderbyDirection','query','objectType','type','damType','userType','author',
        'userName','lastUpdateTime','start','limit');
        $params = array(
            'limit' => 25,
            'start' => 0
        );
        foreach ($queryParams as $keyQueryParams => $param) {
            if($keyQueryParams == 'constrainToSite' && $param && isset($queryParams['siteId'])){
                $params['navigation'][] = (string) $queryParams['siteId'];
            } else if($keyQueryParams == 'predefinedFacets') {
                $this->parsePrefedinedFacets($params, $queryParams);
            } else if (in_array($keyQueryParams, $blockConfigArray)){
                $params['block-config'][$keyQueryParams] = $param;
            } else if (in_array($keyQueryParams, $searchParamsArray)){
                $params[$keyQueryParams] = $param;
            } else if($keyQueryParams == 'taxonomies'){
                $taxonomies = JSON::decode($param);
                foreach($taxonomies as $taxonomy => $verbs){
                    $params[$taxonomy] = $verbs;
                }
            }
        }
        return $params;
    }

    protected function parsePrefedinedFacets(&$params, $queryParams)
    {
        $predefParamsArray = Json::decode($queryParams['predefinedFacets'], Json::TYPE_ARRAY);
        if (is_array($predefParamsArray)) {
            if(isset($predefParamsArray['query'])&&isset($queryParams['query'])&&$predefParamsArray['query']!=$queryParams['query']){
                $inter = $predefParamsArray['query'].' '.$queryParams['query'];
                $predefParamsArray['query'] = $inter;
                $queryParams['query'] = $inter;
            }
            foreach ($predefParamsArray as $key => $value) {
                $params[$key] = $value;
            }
        }
    }

    protected function injectDataInResults(&$results)
    {

    }
}