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

namespace RubedoAPI\Rest\V1\Ecommerce;

use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\ContentsResource;
use WebTales\MongoFilters\Filter;

/**
 * Class ProductsResource
 * @package RubedoAPI\Rest\V1\Ecommerce
 */
class ProductsResource extends ContentsResource
{
    /**
     * @var array
     */
    protected $returnedEntityFields = array(
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
        'productProperties',
    );

    /**
     * define verbs
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('Products')
            ->setDescription('Deal with products')
            ->editVerb('get', function (VerbDefinitionEntity &$definition) {
                $this->defineGet($definition);
            })
            ->editVerb('post', function (VerbDefinitionEntity &$definition) {
                $this->definePost($definition);
            });
        $this
            ->entityDefinition
            ->setName('Product')
            ->setDescription('Works on single product')
            ->editVerb('get', function (VerbDefinitionEntity &$definition) {
                $this->defineEntityGet($definition);
            })
            ->editVerb('patch', function (VerbDefinitionEntity &$definition) {
                $this->defineEntityPatch($definition);
            });
    }

    /**
     * redefine get action
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function defineGet(VerbDefinitionEntity &$definition)
    {
        parent::defineGet($definition);
        $definition
            ->setDescription('Get a list of products')
            ->editOutputFilter('contents', function (FilterDefinitionEntity &$entity) {
                $entity
                    ->setDescription('List of contents');
            });
    }

    /**
     * redefine post action
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function definePost(VerbDefinitionEntity &$definition)
    {
        parent::definePost($definition);
        $definition
            ->setDescription('Post a new product')
            ->editInputFilter('content', function (FilterDefinitionEntity &$entity) {
                $entity
                    ->setDescription('The product to post');
            });
    }

    /**
     * redefine get on entity
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function defineEntityGet(VerbDefinitionEntity &$definition)
    {
        parent::defineEntityGet($definition);
        $definition
            ->setDescription('Get a product')
            ->editOutputFilter('content', function (FilterDefinitionEntity &$entity) {
                $entity
                    ->setDescription('The product');
            });
    }

    /**
     * redefine patch on entity
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function defineEntityPatch(VerbDefinitionEntity &$definition)
    {
        parent::defineEntityPatch($definition);
        $definition
            ->setDescription('Patch a product')
            ->editInputFilter('content', function (FilterDefinitionEntity &$entity) {
                $entity
                    ->setDescription('The product');
            });
    }

    /**
     * Return filter for a product
     *
     * @return $this
     */
    protected function productFilter()
    {
        return Filter::factory('And')
            ->addFilter(Filter::factory('OperatorToValue')->setName('isProduct')->setOperator('$exists')->setValue(true))
            ->addFilter(Filter::factory('Value')->setName('isProduct')->setValue(true));
    }
}
