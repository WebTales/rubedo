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
use Zend\Json\Json;

/**
 * Class MediaRessource
 *
 * @package RubedoAPI\Rest\V1
 */
class MediaRessource extends AbstractRessource
{
    protected $toExtractFromFields = array('title');
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    public function optionsAction()
    {
        return array_merge(parent::optionsAction(), $this->getMediaMeans());
    }

    public function postAction($params)
    {
        $type = $this->getDamTypesCollection()->findById($params['typeId']);
        if (empty($type)) {
            throw new APIEntityException('Type not exist', 404);
        }
        if(!isset($params['fields'])) {
            $params['fields'] = array();
        }
        $nativeLanguage = $params['lang']->getLocale();
        $media = $this->getMediaFromExtractedFields($params['fields']);
        $media['fields'] = $this->filterFields($params['fields'], $type);
        $media['typeId'] = $type['id'];
        $media['directory'] = empty($params['directory'])?'notFiled':$params['directory'];
        $media['mainFileType'] = $type['mainFileType'];
        $media['taxonomy'] = empty($params['taxonomy'])?null:Json::decode($params['taxonomy'], Json::TYPE_ARRAY);
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

    private function uploadFile($file, &$mimeType)
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
        if (! $result['success']) {
            throw new APIEntityException('Failed to create file', 500);
        }
        return $result['data']['id'];

    }

    protected function getMediaFromExtractedFields($fields)
    {
        foreach ($this->toExtractFromFields as $field) {
            if (empty($fields[$field])) {
                throw new APIRequestException(sprintf('Field "%s" is missing', $field), 400);
            }
        }

        $media = array();
        foreach ($fields as $fieldName => &$fieldValue) {
            if (in_array($fieldName,$this->toExtractFromFields)) {
                $media[$fieldName] = $fieldValue;
            }
        }
        return $media;
    }

    protected function filterFields($fields, $type)
    {
        $existingFields = $this->toExtractFromFields;
        foreach ($type['fields'] as $fieldType) {
            $existingFields[] = $fieldType['config']['name'];
        }
        foreach($fields as $fieldName => &$fieldValue) {
            if (!in_array($fieldName, $existingFields)) {
                unset($fields[$fieldName]);
            }
        }

        return $fields;
    }

    public function getEntityAction($id)
    {
        $media = $this->getDamCollection()->findById($id);
        $media['url'] = $this->getUrlAPIService()->mediaUrl($media['id']);
        return array(
            'success' => true,
            'media' => $media
        );
    }

    public function patchEntityAction($id, $params)
    {
        AbstractLocalizableCollection::setIncludeI18n(true);
        $media = $this->getDamCollection()->findById($id);
        $type = $this->getDamTypesCollection()->findById($media['typeId']);
        if (empty($type)) {
            throw new APIEntityException('Type no longer exist', 404);
        }
        $locale = $params['lang']->getLocale();
        if(isset($params['fields'])) {
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
            $media['directory'] = empty($params['directory'])?'notFiled':$params['directory'];
        }
        if (isset($params['taxonomy'])) {
            $media['taxonomy'] = empty($params['taxonomy'])?null:Json::decode($params['taxonomy'], Json::TYPE_ARRAY);
        }

        $returnArray = $this->getDamCollection()->update($media);
        if (!$returnArray['success']) {
            throw new APIEntityException('Media not updated', 500);
        }
        return array(
            'success' => true,
        );
    }

    protected function getMediaMeans()
    {
        return [
            'means' => [
                'search' => '/api/v1/media/search',
            ]
        ];
    }

    protected function define()
    {
        $this
            ->definition
            ->setName('Media')
            ->setDescription('Deal with media')
            ->editVerb('post', function(VerbDefinitionEntity &$verbDef) {
                $this->definePost($verbDef);
            });
        $this
            ->entityDefinition
            ->setName('Media')
            ->setDescription('Deal with a media')
            ->editVerb('get', function(VerbDefinitionEntity &$verbDef) {
                $this->defineGetEntity($verbDef);
            })
            ->editVerb('patch', function(VerbDefinitionEntity &$verbDef) {
                $this->definePatchEntity($verbDef);
            });
    }

    protected function definePost(VerbDefinitionEntity &$verbDef)
    {
        $verbDef
            ->setDescription('add a new')
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

    protected function definePatchEntity(VerbDefinitionEntity $verbDef)
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