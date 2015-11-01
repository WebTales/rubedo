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


    /**
     * Filter contents
     *
     * @param $contents
     * @param $params
     * @return mixed
     */
    protected function outputContentsMask($contents, $params, $query)
    {
        $fields = isset($params['fields']) ? $params['fields'] : array('text', 'summary', 'image');
        $queryReturnedFields = !empty($query["returnedFields"]) && is_array($query["returnedFields"]) ? $query["returnedFields"] : array();
        $fields = array_merge($fields, $queryReturnedFields);
        $urlService = $this->getUrlAPIService();
        if (isset($params['pageId'],$params['siteId'])){
            $page = $this->getPagesCollection()->findById($params['pageId']);
            $site = $this->getSitesCollection()->findById($params['siteId']);
        }

        $mask = array('isProduct', 'i18n', 'pageId', 'blockId', 'maskId');
        $userTypeId = "*";
        $country = "*";
        $region = "*";
        $postalCode = "*";
        $currentUser = $this->getCurrentUserAPIService()->getCurrentUser();
        if ($currentUser) {
            $userTypeId = $currentUser['typeId'];
            if (isset($currentUser['shippingAddress']['country']) && !empty($currentUser['shippingAddress']['country'])) {
                $country = $currentUser['shippingAddress']['country'];
            }
            if (isset($currentUser['shippingAddress']['regionState']) && !empty($currentUser['shippingAddress']['regionState'])) {
                $region = $currentUser['shippingAddress']['regionState'];
            }
            if (isset($currentUser['shippingAddress']['postCode']) && !empty($currentUser['shippingAddress']['postCode'])) {
                $postalCode = $currentUser['shippingAddress']['postCode'];
            }
        }
        foreach ($contents as &$content) {
            if (isset($content["productProperties"]["variations"])){
                $lowestNoSoPrice=false;
                $lowestFinalPrice=false;
                foreach ($content["productProperties"]["variations"] as &$variation){
                    $specialOffers=isset($variation["specialOffers"])&&is_array($variation["specialOffers"]) ? $variation["specialOffers"] : array();
                    $variation["noSoTPrice"]=$this->getTaxesCollection()->getTaxValue($content['typeId'], $userTypeId, $country, $region, $postalCode, $variation["price"]);
                    $variation["finalPrice"]=$this->getTaxesCollection()->getTaxValue($content['typeId'], $userTypeId, $country, $region, $postalCode, $this->getBetterSpecialOffer($specialOffers,$variation["price"]));
                    if (!$lowestNoSoPrice||$variation["noSoTPrice"]<$lowestNoSoPrice){
                        $lowestNoSoPrice=$variation["noSoTPrice"];
                    }
                    if (!$lowestFinalPrice||$variation["finalPrice"]<$lowestFinalPrice){
                        $lowestFinalPrice=$variation["finalPrice"];
                    }
                }
                $content["productProperties"]["lowestNoSoPrice"]=$lowestNoSoPrice;
                $content["productProperties"]["lowestFinalPrice"]=$lowestFinalPrice;
            }
            $content['fields'] = array_intersect_key($content['fields'], array_flip($fields));
            if (isset($params['pageId'],$params['siteId'])) {
                $content['detailPageUrl'] = $urlService->displayUrlApi($content, 'default', $site,
                    $page, $params['lang']->getLocale(), isset($params['detailPageId']) ? (string)$params['detailPageId'] : null);
            }
            $content = array_diff_key($content, array_flip($mask));
        }
        return $contents;
    }

    protected function getBetterSpecialOffer($offers, $basePrice)
    {
        $actualDate = new \DateTime();
        foreach ($offers as $offer) {
            $beginDate = $offer['beginDate'];
            $endDate = $offer['endDate'];
            $offer['beginDate'] = new \DateTime();
            $offer['beginDate']->setTimestamp($beginDate);
            $offer['endDate'] = new \DateTime();
            $offer['endDate']->setTimestamp($endDate);
            if (
                $offer['beginDate'] <= $actualDate
                && $offer['endDate'] >= $actualDate
                && $basePrice > $offer['price']
            ) {
                $basePrice = $offer['price'];
            }
        }
        return $basePrice;
    }
}
