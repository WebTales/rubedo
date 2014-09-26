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

/**
 * Class ContenttypesResource
 *
 * @package RubedoAPI\Rest\V1
 */
class ContenttypesResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Get a list of content types
     * @return array
     */
    public function getAction()
    {
        $contentTypes = $this->getContentTypesCollection()->getList()['data'];
        foreach ($contentTypes as &$contentType) {
            $contentType = $this->filterContentType($contentType);
        }
        return array(
            'success' => true,
            'contentTypes' => $contentTypes,
        );
    }

    /**
     * Get a content type
     *
     * @param $id
     * @return array
     */
    public function getEntityAction($id)
    {
        $contentType = $this->getContentTypesCollection()->findById($id);
        return array(
            'success' => true,
            'contentType' => $contentType,
        );
    }

    /**
     * Filter a content type
     *
     * @param array $contentType
     * @return array
     */
    protected function filterContentType(array $contentType)
    {
        return array_intersect_key($contentType, array_flip(array('id', 'code', 'dependant', 'dependantTypes', 'type', 'workflow', 'productType', 'manageStock', 'readOnly', 'locale')));
    }

    /**
     * Define the resource
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('ContentTypes')
            ->setDescription('Deal with content types')
            ->editVerb('get', function (VerbDefinitionEntity &$definition) {
                $this->defineGet($definition);
            });
        $this
            ->entityDefinition
            ->setName('ContentType')
            ->setDescription('Deal with content type')
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
            ->setDescription('Get a list of content types')
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('contentTypes')
                    ->setDescription('List of content types')
            );
    }

    /**
     * Define get entity action
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function defineGetEntity($definition)
    {
        $definition
            ->setDescription('Get a content type')
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('contentType')
                    ->setDescription('The content type')
            );
    }
}