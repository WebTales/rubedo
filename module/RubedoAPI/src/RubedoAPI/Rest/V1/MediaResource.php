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
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Exceptions\APIRequestException;
use WebTales\MongoFilters\Filter;
use Zend\Json\Json;

/**
 * Class MediaResource
 *
 * @package RubedoAPI\Rest\V1
 */
class MediaResource extends AbstractResource
{
    /**
     * @var array
     */
    protected $toExtractFromFields = array('title');

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Options action
     *
     * @return array
     */
    public function optionsAction()
    {
        return array_merge(parent::optionsAction(), $this->getMediaMeans());
    }

    /**
     * Post action
     *
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function postAction($params)
    {
        $type = $this->getDamTypesCollection()->findById($params['typeId']);
        if (empty($type)) {
            throw new APIEntityException('Type not exist', 404);
        }
        if (!isset($params['fields'])) {
            $params['fields'] = array();
        }
        $nativeLanguage = $params['lang']->getLocale();
        $media = $this->getMediaFromExtractedFields($params['fields']);
        $media['fields'] = $this->filterFields($params['fields'], $type);
        $media['typeId'] = $type['id'];
        $media['directory'] = empty($params['directory']) ? 'notFiled' : $params['directory'];
        $media['mainFileType'] = $type['mainFileType'];
        $media['taxonomy'] = empty($params['taxonomy']) ? null : Json::decode($params['taxonomy'], Json::TYPE_ARRAY);
        $media['nativeLanguage'] = $nativeLanguage;
        $media['i18n'] = array();
        $media['i18n'][$nativeLanguage] = array();
        $media['i18n'][$nativeLanguage]['fields'] = $media['fields'];
        $media['Content-Type'] = null;
        $media['originalFileId'] = $this->uploadFile($params['file'], $media['Content-Type']);

        AbstractLocalizableCollection::setIncludeI18n(true);
        $returnArray = $this->getDamCollection()->create($media);
        if (!$returnArray['success']) {
            throw new APIEntityException('Media not created', 500);
        }
        return array(
            'success' => true,
            'media' => $returnArray['data'],
        );
    }

    public function getAction($params)
    {
        $pagination = $this->setPaginationValues($params);
        $query = Json::decode(html_entity_decode($params["query"]), Json::TYPE_ARRAY);
        $imageThumbnailHeight = isset($params['imageThumbnailHeight']) ? $params['imageThumbnailHeight'] : 100;
        $imageThumbnailWidth = isset($params['imageThumbnailWidth']) ? $params['imageThumbnailWidth'] : 100;
        $filter = $this->setFilters($query, $params);
        $damService = $this->getDamCollection();
        $damService->toggleLocaleFilters();
        if (!isset($filter['filter'])) {
            $filter['filter'] = null;
        }
        if (!isset($filter['sort'])) {
            $filter['sort'] = null;
        }
        $damCount = $damService->count($filter['filter']);

        $mediaArray = $damService->getList($filter['filter'], $filter['sort'], $pagination['start'], $pagination['limit']);

        foreach ($mediaArray['data'] as &$media) {
            $media['url'] = $this->getUrlAPIService()->imageUrl($media['id']);
            $media['thumbnailUrl'] = $this->getUrlAPIService()->imageUrl($media['id'], $imageThumbnailWidth, $imageThumbnailHeight);
        }

        return [
            'success' => true,
            'media' => $mediaArray,
            'count' => $damCount
        ];
    }

    protected function setFilters($query, $params)
    {
        if ($query != null) {
            $filters = Filter::factory();
            /* Add filters on TypeId and publication */
            $filters->addFilter(Filter::factory('In')->setName('typeId')
                ->setValue($query['DAMTypes']));

            /* Add filter on taxonomy */
            foreach ($query['vocabularies'] as $key => $value) {
                if (isset($value['rule'])) {
                    if ($value['rule'] == "some") {
                        $taxOperator = '$in';
                    } elseif ($value['rule'] == "all") {
                        $taxOperator = '$all';
                    } elseif ($value['rule'] == "someRec") {
                        if (count($value['terms']) > 0) {
                            foreach ($value['terms'] as $child) {
                                $terms = $this->getTaxonomyCollection()->fetchAllChildren($child);
                                foreach ($terms as $taxonomyTerms) {
                                    $value['terms'][] = $taxonomyTerms["id"];
                                }
                            }
                        }
                        $taxOperator = '$in';
                    } else {
                        $taxOperator = '$in';
                    }
                } else {
                    $taxOperator = '$in';
                }
                if (count($value['terms']) > 0) {
                    $filters->addFilter(Filter::factory('OperatorToValue')->setName('taxonomy.' . $key)
                        ->setValue($value['terms'])
                        ->setOperator($taxOperator));
                }
            }
            $filters->addFilter(Filter::factory('In')->setName('target')
                ->setValue(array(
                    $params['pageWorkspace'],
                    'global'
                )));

            /*
             * Add Sort
             */
            if (isset($query['fieldRules'])) {
                foreach ($query['fieldRules'] as $field => $rule) {
                    $sort[] = array(
                        "property" => $field,
                        'direction' => $rule['sort']
                    );
                }
            } else {
                $sort[] = array(
                    'property' => 'id',
                    'direction' => 'DESC'
                );
            }
        } else {
            return array();
        }
        $returnArray = array(
            "filter" => $filters,
            "sort" => isset($sort) ? $sort : null
        );
        return $returnArray;
    }

    protected function setPaginationValues($params)
    {
        $defaultLimit = isset($params['limit']) ? $params['limit'] : 8;
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
     * Upload a file
     *
     * @param $file
     * @param $mimeType
     * @return mixed
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    protected function uploadFile($file, &$mimeType)
    {
        $mimeType = mime_content_type($file['tmp_name']);
        $fileToCreate = array(
            'serverFilename' => $file['tmp_name'],
            'text' => $file['name'],
            'filename' => $file['name'],
            'Content-Type' => isset($mimeType) ? $mimeType : $file['type'],
            'mainFileType' => $file
        );
        $result = $this->getFilesCollection()->create($fileToCreate);
        if (!$result['success']) {
            throw new APIEntityException('Failed to create file', 500);
        }
        return $result['data']['id'];

    }

    /**
     * Get media from extracted fields
     *
     * @param $fields
     * @return array
     * @throws \RubedoAPI\Exceptions\APIRequestException
     */
    protected function getMediaFromExtractedFields($fields)
    {
        foreach ($this->toExtractFromFields as $field) {
            if (empty($fields[$field])) {
                throw new APIRequestException(sprintf('Field "%s" is missing', $field), 400);
            }
        }

        $media = array();
        foreach ($fields as $fieldName => &$fieldValue) {
            if (in_array($fieldName, $this->toExtractFromFields)) {
                $media[$fieldName] = $fieldValue;
            }
        }
        return $media;
    }

    /**
     * Filter fields
     *
     * @param $fields
     * @param $type
     * @return mixed
     */
    protected function filterFields($fields, $type)
    {
        $existingFields = $this->toExtractFromFields;
        foreach ($type['fields'] as $fieldType) {
            $existingFields[] = $fieldType['config']['name'];
        }
        foreach ($fields as $fieldName => &$fieldValue) {
            if (!in_array($fieldName, $existingFields)) {
                unset($fields[$fieldName]);
            }
        }

        return $fields;
    }

    /**
     * Get entity action
     * @param $id
     * @return array
     */
    public function getEntityAction($id)
    {
        $media = $this->getDamCollection()->findById($id);
        $media['url'] = $this->getUrlAPIService()->mediaUrl($media['id']);
        return array(
            'success' => true,
            'media' => $media
        );
    }

    /**
     * Post entity action
     *
     * @param $id
     * @param $params
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function postEntityAction($id, $params)
    {
        AbstractLocalizableCollection::setIncludeI18n(true);
        $media = $this->getDamCollection()->findById($id);
        $type = $this->getDamTypesCollection()->findById($media['typeId']);
        if (empty($type)) {
            throw new APIEntityException('Type no longer exist', 404);
        }
        $locale = $params['lang']->getLocale();
        if (isset($params['fields'])) {
            $fields = $this->filterFields($params['fields'], $type);
            if ($locale === $media['nativeLanguage']) {
                $media['fields'] = array_replace_recursive($media['fields'], $fields);
                $media = array_replace_recursive($media, $this->getMediaFromExtractedFields($params['fields']));
            }
            if (!isset($media['i18n'])) {
                $media['i18n'] = array();
            }
            if (!isset($media['i18n'][$locale])) {
                $media['i18n'][$locale] = array();
            }
            if (isset($media['i18n'][$locale]['fields'])) {
                $media['i18n'][$locale]['fields'] = array_replace_recursive($media['i18n'][$locale]['fields'], $fields);
            } else {
                $media['i18n'][$locale]['fields'] = $fields;
            }
        }
        if (isset($params['file'])) {
            $media['Content-Type'] = null;
            $media['originalFileId'] = $this->uploadFile($params['file'], $media['Content-Type']);
        }
        if (isset($params['directory'])) {
            $media['directory'] = empty($params['directory']) ? 'notFiled' : $params['directory'];
        }
        if (isset($params['taxonomy'])) {
            $media['taxonomy'] = empty($params['taxonomy']) ? null : Json::decode($params['taxonomy'], Json::TYPE_ARRAY);
        }

        $returnArray = $this->getDamCollection()->update($media);
        if (!$returnArray['success']) {
            throw new APIEntityException('Media not updated', 500);
        }
        return array(
            'success' => true,
        );
    }

    /**
     * Get media means
     *
     * @return array
     */
    protected function getMediaMeans()
    {
        return [
            'means' => [
                'search' => '/api/v1/media/search',
            ]
        ];
    }

    /**
     * Define
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('Media')
            ->setDescription('Deal with media')
            ->editVerb('post', function (VerbDefinitionEntity &$verbDef) {
                $this->definePost($verbDef);
            })
            ->editVerb('get', function (VerbDefinitionEntity &$verbDef) {
                $this->defineGet($verbDef);
            });
        $this
            ->entityDefinition
            ->setName('Media')
            ->setDescription('Deal with a media')
            ->editVerb('get', function (VerbDefinitionEntity &$verbDef) {
                $this->defineGetEntity($verbDef);
            })
            ->editVerb('post', function (VerbDefinitionEntity &$verbDef) {
                $this->definePostEntity($verbDef);
            });
    }

    /**
     * Define post
     *
     * @param VerbDefinitionEntity $verbDef
     */
    protected function definePost(VerbDefinitionEntity &$verbDef)
    {
        $verbDef
            ->setDescription('add a new media')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('File')
                    ->setKey('file')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Dam type')
                    ->setKey('typeId')
                    ->setFilter('\MongoId')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Directory')
                    ->setKey('directory')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Fields for the media')
                    ->setKey('fields')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Taxonomies for the media')
                    ->setKey('taxonomy')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Media')
                    ->setKey('media')
                    ->setRequired()
            );
    }

    protected function defineGet(VerbDefinitionEntity &$verbDef)
    {
        $verbDef
            ->setDescription('Get multiple Media from query')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('start')
                    ->setDescription('Item\'s index number to start')
                    ->setFilter('int')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('limit')
                    ->setDescription('How much media to return')
                    ->setFilter('int')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('imageThumbnailWidth')
                    ->setDescription('Width of the thumbnail')
                    ->setFilter('int')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('imageThumbnailHeight')
                    ->setDescription('Height of the thumbnail')
                    ->setFilter('int')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('query')
                    ->setFilter('string')
                    ->setKey('query')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Workspace of the current page')
                    ->setKey('pageWorkspace')
                    ->setFilter('string')
                    ->setRequired()
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
    }

    /**
     * Define get entity
     *
     * @param VerbDefinitionEntity $verbDef
     */
    protected function defineGetEntity(VerbDefinitionEntity &$verbDef)
    {
        $verbDef
            ->setDescription('Get a media')
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Media')
                    ->setKey('media')
                    ->setRequired()
            );
    }

    /**
     * Define post entity
     *
     * @param VerbDefinitionEntity $verbDef
     */
    protected function definePostEntity(VerbDefinitionEntity $verbDef)
    {
        $verbDef
            ->setDescription('Patch a media')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('File')
                    ->setKey('file')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Directory')
                    ->setKey('directory')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Fields for the media')
                    ->setKey('fields')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Taxonomies for the media')
                    ->setKey('taxonomy')
            );
    }
}