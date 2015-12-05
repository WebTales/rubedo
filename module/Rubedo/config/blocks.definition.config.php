<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2014, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
$blocksPath = realpath(__DIR__ . "/blocks/");

/**
 * List default Rubedo blocks
 */
return array(
    'addThis' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/addThis.json'
    ),
    'addThisFollow' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/addThisFollow.json'
    ),
    'unsubscribe' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/unsubscribe.json'
    ),
    'audio' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/audio.json'
    ),
    'authentication' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/authentication.json'
    ),
    'breadcrumb' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/breadcrumb.json'
    ),
    'calendar' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/calendar.json'
    ),
    'carrousel' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/carrousel.json'
    ),
    'contact' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/contact.json'
    ),
    'contentDetail' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/contentDetail.json'
    ),
    'contentList' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/contentList.json'
    ),
    'damList' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/damList.json'
    ),
    'externalMedia' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/externalMedia.json'
    ),
    'geoSearchResults' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/geoSearchResults.json'
    ),
    'image' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/image.json'
    ),
    'imageGallery' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/imageGallery.json'
    ),
    'imageMap' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/imageMap.json'
    ),
    'languageMenu' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/languageMenu.json'
    ),
    'mailingList' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/mailingList.json'
    ),
    'navigation' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/navigation.json'
    ),
    'protectedResource' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/protectedResource.json'
    ),
    'resource' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/resource.json'
    ),
    'signUp' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/signUp.json'
    ),
    'richText' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/richText.json'
    ),
    'searchForm' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/searchForm.json'
    ),
    'searchResults' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/searchResults.json'
    ),
    'directory' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/directory.json'
    ),
    'userProfile' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/userProfile.json'
    ),
    'simpleText' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/simpleText.json'
    ),
    'siteMap' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/siteMap.json'
    ),
    'twitter' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/twitter.json'
    ),
    'video' => array(
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/video.json'
    ),
    'd3Script' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/d3Script.json'
    ),
    'category' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/category.json'
    ),
    'shoppingCart' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/shoppingCart.json'
    ),
    'development' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/development.json'
    ),
    'checkout' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/checkout.json'
    ),
    'userOrders' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/userOrders.json'
    ),
    'orderDetail' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/orderDetail.json'
    ),
    'productList' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/productList.json'
    ),
    'productSearch' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/productSearch.json'
    ),
    'contentContribution' => array(
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/contentContribution.json'
    ),
    'megaMenu' => array(
    		'maxlifeTime' => 60,
    		'definitionFile' => $blocksPath . '/megaMenu.json'
    ),
    'rssFeed' => array(
    		'maxlifeTime' => 60,
    		'definitionFile' => $blocksPath . '/rssFeed.json'
    ),
);