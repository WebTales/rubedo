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
        $nativeLanguage = $params['lang'];
        $media = $this->getMediaFromExtractedFields($params['fields']);
        $media['fields'] = $this->filterFields($params['fields'], $type);
        $media['typeId'] = $type['id'];
        $media['directory'] = empty($params['directory'])?'notFiled':$params['directory'];
        $media['mainFileType'] = $type['mainFileType'];
        $media['taxonomy'] = empty($params['taxonomy'])?null:Json::decode($params['taxonomy'], Json::TYPE_ARRAY);

        return array(
            'success' => true,
            'media' => $params,
        );
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

        var_dump($fields); exit;
//        foreach ($type[])
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
        return array(
            'success' => true,
            'media' => $media
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
                    ->setRequired()
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
}