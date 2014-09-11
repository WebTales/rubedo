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

use Rubedo\Collection\AbstractLocalizableCollection;
use RubedoAPI\Exceptions\APIAuthException;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use WebTales\MongoFilters\Filter;


/**
 * Class ContentsResource
 * @package RubedoAPI\Rest\V1
 */
class ContentsResource extends AbstractResource
{
    protected $toExtractFromFields = array('text');
    protected $otherLocalizableFields = array('text', 'summary');

    /**
     * { @inheritdoc }
     */
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

        $queryId = & $params['queryId'];

        $filters = $this->getQueriesCollection()->getFilterArrayById($queryId);

        if (isset($params['date']) && isset($params['dateFieldName'])){
            if(isset($params['endDate'])){
                $timestamp = $params['date'];
                $nextMonthTimeStamp = $params['endDate'];
            } else {
                $dateArray = getdate($params['date']);
                $mounth = $dateArray['mon'];
                $year = $dateArray['year'];
                $date = new \DateTime();
                $date->setDate($year,$mounth,1);
                $date->setTime(0,0,0);
                $timestamp = (string) $date->getTimestamp();
                if ($mounth < 12){
                    $date->setDate($year, $mounth + 1, 1);
                } else {
                    $date->setDate($year + 1, 1, 1);
                }
                $nextMonthTimeStamp = (string) $date->getTimestamp();
            }

            $eventStartInCurrentlyMonth = Filter::factory('And')
                ->addFilter(Filter::factory('OperatorTovalue')->setName('fields.'.$params['dateFieldName'])
                    ->setOperator('$gte')
                    ->setValue($timestamp))
                ->addFilter(Filter::factory('OperatorTovalue')->setName('fields.'.$params['dateFieldName'])
                    ->setOperator('$lt')
                    ->setValue($nextMonthTimeStamp));

            if(isset($params['endDateFieldName'])){
                $eventStartingBeforeCurrenltyMonth = Filter::factory('And')
                    ->addFilter(Filter::factory('OperatorTovalue')
                        ->setName('fields.'.$params['dateFieldName'])
                        ->setOperator('$lt')
                        ->setValue($timestamp))
                ->addFilter(Filter::factory('OperatorTovalue')
                    ->setName('fields.'.$params['endDateFieldName'])
                    ->setOperator('$gte')
                    ->setValue($timestamp));

                $eventsInMonth = Filter::factory('Or')
                    ->addFilter($eventStartingBeforeCurrenltyMonth)
                    ->addFilter($eventStartInCurrentlyMonth);
            } else {
                $eventsInMonth = $eventStartInCurrentlyMonth;
            }
            $filters['filter']->addFilter($eventsInMonth);
        }


        if ($filters === false) {
            throw new APIEntityException('Query not found', 404);
        }

        if ($filters !== false) {
            $queryType = $filters["queryType"];
            $query = $this->getQueriesCollection()->getQueryById($queryId);

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

    /**
     * Add the new content
     *
     * @param $params
     * @throws \RubedoAPI\Exceptions\APIAuthException
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @return array
     */
    public function postAction($params)
    {
        $data = &$params['content'];
        if (empty($data['typeId'])) {
            throw new APIEntityException('typeId data is missing.', 400);
        }

        $type = $this->getContentTypesCollection()->findById($data['typeId']);
        if (empty($type)) {
            throw new APIEntityException('ContentType not found.', 404);
        }

        foreach ($data['fields'] as $fieldName => $fieldValue) {
            if (in_array($fieldName, $this->toExtractFromFields)) {
                $data[$fieldName] = $fieldValue;
            }
        }

        if (!isset($data['i18n'])) {
            $data['i18n'] = array();
        }

        if (!isset($data['i18n'][$params['lang']->getLocale()])) {
            $data['i18n'][$params['lang']->getLocale()] = array();
        }

        $data['i18n'][$params['lang']->getLocale()]['fields'] = $this->localizableFields($type, $data['fields']);

        $data['fields'] = $this->filterFields($type, $data['fields']);

        if (!isset($data['status'])) {
            $data['status'] = 'published';
        }

        if (!isset($data['target'])) {
            $data['target'] = array();
        }

        if (!isset($data['nativeLanguage'])) {
            $data['nativeLanguage'] = $params['lang']->getLocale();
        }

        if (!$this->getAclService()->hasAccess('write.ui.contents.' . $data['status'])) {
            throw new APIAuthException('You have no suffisants rights', 403);
        }

        return $this->getContentsCollection()->create($data, array(), false);
    }

    /**
     * Remove fields if not in content type
     *
     * @param $type
     * @param $fields
     */
    protected function filterFields($type, $fields)
    {
        $existingFields = array();
        foreach ($type['fields'] as $field) {
            if (!($field['config']['localizable'] || in_array($field['config']['name'], $this->otherLocalizableFields))) {
                $existingFields[] = $field['config']['name'];
            }
        }
        foreach ($fields as $key => $value) {
            if (!in_array($key, $existingFields)) {
                unset ($fields[$key]);
            }
        }
        return $fields;
    }

    /**
     * Return localizable fields if not in content type
     *
     * @param $type
     * @param $fields
     */
    protected function localizableFields($type, $fields)
    {
        $existingFields = array();
        foreach ($type['fields'] as $field) {
            if ($field['config']['localizable']) {
                $existingFields[] = $field['config']['name'];
            }
        }
        foreach ($fields as $key => $value) {
            if (!(in_array($key, $existingFields) || in_array($key, $this->otherLocalizableFields))) {
                unset ($fields[$key]);
            }
        }
        return $fields;
    }
    /**
     * Filter contents
     *
     * @param $contents
     * @param $params
     * @return mixed
     */
    protected function outputContentsMask($contents, $params)
    {
        $fields = isset($params['fields']) ? $params['fields'] : array('text', 'summary', 'image');
        $urlService = $this->getUrlAPIService();
        $page = $this->getPagesCollection()->findById($params['pageId']);
        $site = $this->getSitesCollection()->findById($params['siteId']);
        $mask = array('isProduct', 'productProperties', 'i18n', 'pageId', 'blockId', 'maskId');
        foreach ($contents as &$content) {
            $content['fields'] = array_intersect_key($content['fields'], array_flip($fields));
            $content['detailPageUrl'] = $urlService->displayUrlApi($content, 'default', $site,
                $page, $params['lang']->getLocale(), isset($params['detailPageId']) ? (string) $params['detailPageId'] : null);
            $content = array_diff_key($content, array_flip($mask));
        }
        return $contents;
    }

    /**
     * Get content list
     *
     * @param $filters
     * @param $pageData
     * @param bool $ismagic
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    protected function getContentList($filters, $pageData, $ismagic = false)
    {
        $filters["sort"] = isset($filters["sort"]) ? $filters["sort"] : array();
        $contentArray = $this->getContentsCollection()->getOnlineList($filters["filter"], $filters["sort"], $pageData['start'], $pageData['limit'], $ismagic);
        $contentArray['page'] = $pageData;
        if ($contentArray['count'] < $pageData['start']) {
            throw new APIEntityException('There is only ' . $contentArray['count'] . ' contents. Start parameter must be inferior of this value', 404);
        }
        return $contentArray;
    }

    /**
     * Set pagination value
     *
     * @param $params
     * @return mixed
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    protected function setPaginationValues($params)
    {
        $defaultLimit = isset($params['limit']) ? $params['limit'] : 6;
        $defaultStart = isset($params['start']) ? $params['start'] : 0;
        if ($defaultStart < 0) {
            throw new APIEntityException('Start paramater must be >= 0', 404);
        }
        if ($defaultLimit < 1) {
            throw new APIEntityException('Limit paramater must be >= 1', 404);
        }
        $pageData['start'] = $defaultStart;
        $pageData['limit'] = $defaultLimit;
        return $pageData;
    }

    /**
     * Patch a content
     *
     * @param $id
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIAuthException
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function patchEntityAction($id, $params)
    {
        AbstractLocalizableCollection::setIncludeI18n(true);
        $content = $this->getContentsCollection()->findById($id, false, false);
        if (empty($content)) {
            throw new APIEntityException('Content not found', 404);
        }
        $data = &$params['content'];
        $type = $this->getContentTypesCollection()->findById(empty($data['typeId'])?$content['typeId']:$data['typeId']);
        if (empty($type)) {
            throw new APIEntityException('ContentType not found.', 404);
        }

        if (isset($data['fields'])) {
            if ($content['nativeLanguage'] === $params['lang']->getLocale()) {
                foreach ($data['fields'] as $fieldName => $fieldValue) {
                    if (in_array($fieldName, $this->toExtractFromFields)) {
                        $data[$fieldName] = $fieldValue;
                    }
                }
            }
            if (!isset($data['i18n'])) {
                $data['i18n'] = array();
            }
            if (!isset($data['i18n'][$params['lang']->getLocale()])) {
                $data['i18n'][$params['lang']->getLocale()] = array();
            }
            $data['i18n'][$params['lang']->getLocale()]['fields'] = $this->localizableFields($type, $data['fields']);
            $data['fields'] = $this->filterFields($type, $data['fields']);
        }

        if (isset($data['status']) && !$this->getAclService()->hasAccess('write.ui.contents.' . $data['status'])) {
            throw new APIAuthException('You have no suffisants rights', 403);
        }

        $content = array_replace_recursive($content, $data);
        return $this->getContentsCollection()->update($content, array(), false);
    }

    /**
     * Get to contents/{id}
     *
     * @param $id
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @throws \RubedoAPI\Exceptions\APIRequestException
     */
    public function getEntityAction($id, $params)
    {
        $content = $this->getContentsCollection()->findById($id, true, false);
        if (empty($content)) {
            throw new APIEntityException('Content not found', 404);
        }

        $contentType = $this->getContentTypesCollection()->findById($content['typeId'], true, false);
        if (empty($contentType)) {
            throw new APIEntityException('ContentType not found', 404);
        }

        if (isset($params['fingerprint'])) {
            $currentTime = $this->getCurrentTimeService()->getCurrentTime();
            //get user fingerprint
            $this->getContentViewLogCollection()->log($content['id'], $content['locale'], $params['fingerprint'], $currentTime);
            //rebuild user recommendations if necessary
            $emptyFilter = Filter::factory();
            $oldestView = $this->getContentViewLogCollection()->findOne($emptyFilter);
            if ($oldestView) {
                $timeSinceLastRun = $currentTime - $oldestView['timestamp'];
                if ($timeSinceLastRun > 60) {
                    $curlUrl = 'http://'.$_SERVER['HTTP_HOST'].'/queue?service=UserRecommendations&class=build';
                    $curly = curl_init();
                    curl_setopt($curly, CURLOPT_URL, $curlUrl);
                    curl_setopt($curly, CURLOPT_FOLLOWLOCATION, true);  // Follow the redirects (needed for mod_rewrite)
                    curl_setopt($curly, CURLOPT_HEADER, false);         // Don't retrieve headers
                    curl_setopt($curly, CURLOPT_NOBODY, true);          // Don't retrieve the body
                    curl_setopt($curly, CURLOPT_RETURNTRANSFER, true);  // Return from curl_exec rather than echoing
                    curl_setopt($curly, CURLOPT_FRESH_CONNECT, true);   // Always ensure the connection is fresh
                    // Timeout super fast once connected, so it goes into async.
                    curl_setopt($curly, CURLOPT_TIMEOUT, 1);
                    curl_exec($curly);
                }
            }
        }

        $content = array_intersect_key(
            $content,
            array_flip(
                array(
                    'id',
                    'text',
                    'version',
                    'createUser',
                    'lastUpdateUser',
                    'fields',
                    'taxonomy',
                    'status',
                    'pageId',
                    'maskId',
                    'locale',
                    'readOnly',
                )
            )
        );

        if (isset($params['fields'])) {
            if (!is_array($params['fields']))
                throw new APIRequestException('"fields" must be an array', 400);
            $content['fields'] = array_intersect_key($content['fields'], array_flip($params['fields']));
        }

        $content['type'] = array_intersect_key(
            $contentType,
            array_flip(
                array(
                    'id',
                    'code',
                    'activateDisqus',
                    'fields',
                    'locale',
                    'version',
                    'workflow',
                    'readOnly',
                )
            )
        );

        return [
            'success' => true,
            'content' => $content,
        ];
    }

    /**
     * Define the resource
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('Contents')
            ->setDescription('Deal with contents')
            ->editVerb('get', function (VerbDefinitionEntity &$definition) {
                $this->defineGet($definition);
            })
            ->editVerb('post', function (VerbDefinitionEntity &$definition) {
                $this->definePost($definition);
            });
        $this
            ->entityDefinition
            ->setName('Content')
            ->setDescription('Works on single content')
            ->editVerb('get', function (VerbDefinitionEntity &$definition) {
                $this->defineEntityGet($definition);
            })
            ->editVerb('patch', function (VerbDefinitionEntity &$definition) {
                $this->defineEntityPatch($definition);
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
                    ->setKey('fields')
                    ->setDescription('Mask of fields')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('dateFieldName')
                    ->setDescription('Name of the date field for the query')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('endDateFieldName')
                    ->setDescription('Name of the endDate field for the query')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('date')
                    ->setDescription('Date filter for the query')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('endDate')
                    ->setDescription('endDate filter for the query')
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
            );
    }

    /**
     * Define post
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function definePost(VerbDefinitionEntity &$definition) {
        $definition
            ->setDescription('Post a new content')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('The content to post')
                    ->setKey('content')
                    ->setMultivalued()
            )
            ->identityRequired();
    }

    /**
     * Define get entity
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function defineEntityGet(VerbDefinitionEntity &$definition)
    {
        $definition
            ->setDescription('Get a content')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Fields to return')
                    ->setKey('fields')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('The content')
                    ->setKey('content')
                    ->setRequired()
            );
    }

    /**
     * Define get entity
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function defineEntityPatch(VerbDefinitionEntity &$definition)
    {
        $definition
            ->setDescription('Patch a content')
            ->identityRequired()
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('The content')
                    ->setKey('content')
                    ->setRequired()
            );
    }
}