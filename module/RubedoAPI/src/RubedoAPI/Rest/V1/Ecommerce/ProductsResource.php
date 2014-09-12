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

class ProductsResource extends ContentsResource
{
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

    protected function definePost(VerbDefinitionEntity &$definition) {
        parent::definePost($definition);
        $definition
            ->setDescription('Post a new product')
            ->editInputFilter('content', function(FilterDefinitionEntity &$entity) {
                $entity
                    ->setDescription('The product to post');
            });
    }

    protected function defineEntityGet(VerbDefinitionEntity &$definition)
    {
        parent::defineEntityGet($definition);
        $definition
            ->setDescription('Get a product')
            ->editOutputFilter('content', function(FilterDefinitionEntity &$entity) {
                $entity
                    ->setDescription('The product');
            });
    }

    protected function defineEntityPatch(VerbDefinitionEntity &$definition)
    {
        parent::defineEntityPatch($definition);
        $definition
            ->setDescription('Patch a product')
            ->editInputFilter('content', function(FilterDefinitionEntity &$entity) {
                $entity
                    ->setDescription('The product');
            });
    }

    protected function productFilter()
    {
        return Filter::factory('And')
            ->addFilter(Filter::factory('OperatorToValue')->setName('isProduct')->setOperator('$exists')->setValue(true))
            ->addFilter(Filter::factory('Value')->setName('isProduct')->setValue(true));
    }
}
