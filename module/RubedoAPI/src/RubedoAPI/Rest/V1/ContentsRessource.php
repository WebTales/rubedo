<?php

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIEntityException;

/**
 * Class AbstractRessource
 * @package RubedoAPI\Rest\V1
 */

class ContentsRessource extends AbstractRessource {
    private $contentsServices;

    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Contents')
            ->setDescription('Deal with contents')
            ->editVerb('get', function(VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a list of contents')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('queryId')
                            ->setRequired()
                            ->setDescription('Id of the query')
                            ->setFilter('\\MongoId')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('siteId')
                            ->setRequired()
                            ->setDescription('Id of the site')
                            ->setFilter('\\MongoId')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('pageId')
                            ->setRequired()
                            ->setDescription('Id of the page')
                            ->setFilter('\\MongoId')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('detailPageId')
                            ->setDescription('Id of the linked page')
                            ->setFilter('\\MongoId')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('lang')
                            ->setRequired()
                            ->setDescription('Locale')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('fields')
                            ->setDescription('Mask of fields')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('isMagic')
                            ->setDescription('Property is Magic query')
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
                            ->setKey('contents')
                            ->setDescription('List of contents')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('count')
                            ->setDescription('Number of all contents')
                    )
                ;
            })
        ;
    }
    public function getAction($params)
    {
        $this->contentsServices = Manager::getService('Contents');
        $queriesServices = Manager::getService('Queries');

        $queryId = $params['queryId'];

        $filters = $queriesServices->getFilterArrayById($queryId);

        if ($filters === false){
            throw new APIEntityException('Query not found', 404);
        }

        if ($filters !== false) {
            $queryType = $filters["queryType"];
            $query = $queriesServices->getQueryById($queryId);

            if ($queryType === "manual" && $query != false && isset($query['query']) && is_array($query['query'])) {
                $contentOrder = $query['query'];
                $keyOrder = array();
                $contentArray = array();

                // getList
                $unorderedContentArray = $this->getContentList($filters, $this->setPaginationValues($params));

                foreach ($contentOrder as $value) {
                    foreach ($unorderedContentArray['data'] as $subKey => $subValue) {
                        if ($value === $subValue['id']) {
                            $keyOrder[] = $subKey;
                        }
                    }
                }

                foreach ($keyOrder as $value) {
                    $contentArray["data"][] = $unorderedContentArray["data"][$value];
                }

                $nbItems = $unorderedContentArray["count"];
            } else {
                $ismagic = isset($params['isMagic']) ? $params['isMagic'] : false;
                $contentArray = $this->getContentList($filters, $this->setPaginationValues($params), $ismagic);
                $nbItems = $contentArray["count"];
            }
        } else {
            $nbItems = 0;
        }
        return [
            'success' => true,
            'contents' => $this->outputContentsMask($contentArray['data'], $params),
            'count' => $nbItems
        ];
    }

    protected function outputContentsMask($contents, $params)
    {
        $fields = isset($params['fields'])?$params['fields'] : array('text','summary','image');
        $urlService = $this->getUrlAPIService();
        $page = Manager::getService('Pages')->findById($params['pageId']);
        $site = Manager::getService('Sites')->findById($params['siteId']);
        $mask = array('isProduct', 'productProperties', 'i18n', 'pageId', 'blockId', 'maskId');
        foreach ($contents as &$content){
            $content['fields'] = array_intersect_key($content['fields'], array_flip($fields));
            $content['detailPageUrl'] = $urlService->displayUrlApi($content,'default', $site,
                $page, $params['lang'], isset($params['detailPageId'])?$params['detailPageId']:null);
            $content = array_diff_key($content, array_flip($mask));
        }
        return $contents;
    }

    protected function getContentList($filters, $pageData, $ismagic = false)
    {
        $filters["sort"] = isset($filters["sort"]) ? $filters["sort"] : array();
        $contentArray = $this->contentsServices->getOnlineList($filters["filter"], $filters["sort"], $pageData['start'], $pageData['limit'],$ismagic);
        $contentArray['page'] = $pageData;
        if($contentArray['count']<$pageData['start']){
            throw new APIEntityException('There is only '.$contentArray['count'].' contents. Start parameter must be inferior of this value', 404);
        }
        return $contentArray;
    }

    protected function setPaginationValues($params)
    {
        $defaultLimit = isset($params['limit'])?$params['limit'] : 6;
        $defaultStart = isset($params['start'])?$params['start'] : 0;
        if($defaultStart < 0){
            throw new APIEntityException('Start paramater must be >= 0', 404);
        }
        if($defaultLimit < 1){
            throw new APIEntityException('Limit paramater must be >= 1', 404);
        }
        $pageData['start'] = $defaultStart;
        $pageData['limit'] = $defaultLimit;
        return $pageData;
    }
}