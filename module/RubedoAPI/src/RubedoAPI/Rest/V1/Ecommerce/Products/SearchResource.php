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

namespace RubedoAPI\Rest\V1\Ecommerce\Products;

use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\SearchResource as GlobalSearch;
use Zend\Json\Json;

/**
 * Class SearchResource
 * @package RubedoAPI\Rest\V1\Ecommerce\Products
 */
class SearchResource extends GlobalSearch
{
    /**
     * Cache lifetime for api cache (only for get and getEntity)
     * @var int
     */
    public $cacheLifeTime=60;

    public function __construct()
    {
        parent::__construct();
        $this->searchOption = 'content';
        $this
            ->definition
            ->setName('Products')
            ->setDescription('Deal with products')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a list of products using Elastic Search');
            });
    }

    /**
    * Get action
    * @param $queryParams
    * @return array
    */
    public function getAction($queryParams)
    {
        $params = $this->initParams($queryParams);
        $params["isProduct"]=true;
        $query = $this->getElasticDataSearchService();
        $query::setIsFrontEnd(true);
        $query->init();
        //add product param here
        $results = $query->search($params, $this->searchOption);

        $this->injectDataInResults($results, $queryParams);

        return [
            'success' => true,
            'results' => $results,
            'count' => $results['total']
        ];
    }

    /**
     * Inject data in results
     *
     * @param $results
     * @param $params
     */
    protected function injectDataInResults(&$results, $params)
    {
        if (isset($params['profilePageId'])) {
            $urlOptions = array(
                'encode' => true,
                'reset' => true,
            );
            $profilePageUrl = $this->getContext()->url()->fromRoute('rewrite', array(
                'pageId' => $params['profilePageId'],
                'locale' => $params['lang']->getLocale(),
            ), $urlOptions);
        }
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
        $page = $this->getPagesCollection()->findById($params['pageId']);
        $site = $this->getSitesCollection()->findById($params['siteId']);
        foreach ($results['data'] as $key => &$value) {
            $value['url'] = $this->getUrlAPIService()->displayUrlApi($value, 'default', $site, $page, $params['lang']->getLocale(), isset($params['detailPageId']) ? (string)$params['detailPageId'] : null);
            if (isset($value['author'])) {
                $value['authorUrl'] = isset($profilePageUrl) ? $profilePageUrl . '?userprofile=' . $value['id'] : '';
            }
            if (isset($value['encodedProductProperties'][0])&&!empty($value['encodedProductProperties'][0])){
                $value['productProperties']=Json::decode($value['encodedProductProperties'][0],Json::TYPE_ARRAY);
            }
            if (isset($value["productProperties"]["variations"])){
                $lowestNoSoPrice=false;
                $lowestFinalPrice=false;
                foreach ($value["productProperties"]["variations"] as &$variation){
                    $specialOffers=isset($variation["specialOffers"])&&is_array($variation["specialOffers"]) ? $variation["specialOffers"] : array();
                    $variation["noSoTPrice"]=$this->getTaxesCollection()->getTaxValue($value['typeId'], $userTypeId, $country, $region, $postalCode, $variation["price"]);
                    $variation["finalPrice"]=$this->getTaxesCollection()->getTaxValue($value['typeId'], $userTypeId, $country, $region, $postalCode, $this->getBetterSpecialOffer($specialOffers,$variation["price"]));
                    if (!$lowestNoSoPrice||$variation["noSoTPrice"]<$lowestNoSoPrice){
                        $lowestNoSoPrice=$variation["noSoTPrice"];
                    }
                    if (!$lowestFinalPrice||$variation["finalPrice"]<$lowestFinalPrice){
                        $lowestFinalPrice=$variation["finalPrice"];
                    }
                }
                $value["productProperties"]["lowestNoSoPrice"]=$lowestNoSoPrice;
                $value["productProperties"]["lowestFinalPrice"]=$lowestFinalPrice;
            }
        }
        if (isset($params['displayedFacets'])) {
            $this->injectOperatorsInActiveFacets($results, $params);
        }
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