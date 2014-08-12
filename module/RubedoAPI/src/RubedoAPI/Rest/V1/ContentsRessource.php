<?php

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;
use WebTales\MongoFilters\Filter;

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
                            ->setKey('lang')
                            ->setRequired()
                            ->setDescription('Locale')
                            ->setFilter('string')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('fields')
                            ->setDescription('Fields')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('isMagic')
                            ->setDescription('Locale')
                            ->setFilter('boolean')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('contents')
                            ->setDescription('List of contents')
                    )
                ;
            })
        ;
    }
    public function getAction($params)
    {
        $this->contentsServices = Manager::getService('Contents');
        $contentTypesServices = Manager::getService('ContentTypes');
        $queriesServices = Manager::getService('Queries');

        $queryId = $params['queryId'];

        $filters = $queriesServices->getFilterArrayById($queryId);

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
            'contents' => $this->outputContentsMask($contentArray['data'])
        ];
    }

    protected function outputContentsMask($contents, $fields = array('text','summary','image'))
    {
        foreach ($contents as &$content){
            $content['fields'] = array_intersect_key($content['fields'], array_flip($fields));
        }
        return $contents;
    }

    protected function getContentList($filters, $pageData, $ismagic = false)
    {
        $filters["sort"] = isset($filters["sort"]) ? $filters["sort"] : array();
        $contentArray = $this->contentsServices->getOnlineList($filters["filter"], $filters["sort"], (($pageData['currentPage'] - 1) * $pageData['limit']) + $pageData['skip'], $pageData['limit'],$ismagic);
        $contentArray['page'] = $pageData;
        $contentArray['count'] = max(0, $contentArray['count'] - $pageData['skip']);
        return $contentArray;
    }

    protected function setPaginationValues($params)
    {
        $defaultLimit = 6;
        $defaultSkip = 0;
        $pageData['skip'] = $defaultSkip;
        $pageData['limit'] = $defaultLimit;
        $pageData['currentPage'] = 1;
        return $pageData;
    }
}