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

class MediatypesResource extends AbstractResource {
    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Get a list of media types
     * @return array
     */
    public function getAction() {
        $mediaTypes = $this->getDamTypesCollection()->getList()['data'];
        foreach ($mediaTypes as &$mediaType) {
            $mediaType = $this->filterMediaType($mediaType);
        }
        return array(
            'success' => true,
            'mediaTypes' => $mediaTypes,
        );
    }

    /**
     * Get a media type
     *
     * @param $id
     * @return array
     */
    public function getEntityAction($id)
    {
        $mediaType = $this->getDamTypesCollection()->findById($id);
        return array(
            'success' => true,
            'mediaType' => $mediaType,
        );
    }

    /**
     * Filter media type
     *
     * @param array $mediaType
     * @return array
     */
    protected function filterMediaType(array $mediaType) {
        return array_intersect_key($mediaType, array_flip(array('id', 'mainFileType', 'type', 'readOnly', 'locale')));
    }

    /**
     * Define the resource
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('MediaTypes')
            ->setDescription('Deal with media types')
            ->editVerb('get', function (VerbDefinitionEntity &$definition) {
                $this->defineGet($definition);
            });
        $this
            ->entityDefinition
            ->setName('MediaType')
            ->setDescription('Deal with media type')
            ->editVerb('get', function (VerbDefinitionEntity &$definition) {
                $this->defineGetEntity($definition);
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
            ->setDescription('Get a list of media types')
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('mediaTypes')
                    ->setDescription('List of media types')
            );
    }

    /**
     * Define get entity action
     *
     * @param VerbDefinitionEntity $definition
     */
    private function defineGetEntity(VerbDefinitionEntity &$definition)
    {
        $definition
            ->setDescription('Get a media type')
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('mediaType')
                    ->setDescription('The media type')
            );
    }
}