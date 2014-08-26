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

namespace RubedoAPI\Rest\V1\Media;

use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\AbstractRessource;
use Zend\Json\Json;

/**
 * Class SearchRessource
 * @package RubedoAPI\Rest\V1\Media
 */
class SearchRessource extends AbstractRessource
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Media')
            ->setDescription('Deal with media')
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
                            ->setKey('facets')
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
                            ->setKey('media')
                            ->setDescription('List of media')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('count')
                            ->setDescription('Number of all media')
                    );
            });
    }

    public function getAction($queryParams)
    {
        $params = array(
            'orderByDirection' => isset($queryParams['sort']) ? $queryParams['sort'] : 'asc',
            'limit' => isset($queryParams['limit']) ? $queryParams['limit'] : 25,
            'start' => isset($queryParams['start']) ? $queryParams['start'] : 0,
        );

        if (isset($queryParams['constrainToSite']) && isset($queryParams['siteId'])) {
            $params['navigation'][] = $queryParams['siteId'];
        }

        if (isset($queryParams['facets'])) {
            $predefParamsArray = Json::decode($queryParams['facets'], Json::TYPE_ARRAY);
            if (is_array($predefParamsArray)) {
                foreach ($predefParamsArray as $key => $value) {
                    $params[$key] = $value;
                }
            }
        }

        \Rubedo\Elastic\DataSearch::setIsFrontEnd(true);
        $query = Manager::getService('ElasticDataSearch');
        $query->init();
        $results = $query->search($params, 'dam');

        foreach ($results['data'] as $key => $value) {
            $results['data'][$key]['fileSize'] = $this->humanfilesize($value['fileSize']);
            $urlService = $this->getUrlAPIService();
            $results['data'][$key]['url'] = $urlService->mediaUrl($results['data'][$key]['id']);
        }

        return [
            'success' => true,
            'media' => $results,
            'count' => $results['total']
        ];
    }

    protected function humanfilesize ($bytes, $decimals = 0)
    {
        $size = array(
            'B',
            'kB',
            'MB',
            'GB',
            'TB',
            'PB',
            'EB',
            'ZB',
            'YB'
        );
        $factor = floor((strlen($bytes[0]) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes[0] / pow(1024, $factor)) . @$size[$factor];
    }
}