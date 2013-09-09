<?php
// use Zend\Json\Json;
// use Zend\Debug\Debug;

$blocksPath = realpath(__DIR__ . "/blocks/");

// $globalJsonFile = file_get_contents(APPLICATION_PATH.'/public/components/webtales/rubedo-backoffice-ui/www/resources/localisationfiles/generic/blockTypes.json');

// $globalJson = Json::decode($globalJsonFile,Json::TYPE_ARRAY);
// foreach($globalJson as $blockConfig){
//     $blockType = $blockConfig['configBasique']['bType'];
//     $blockJson = Json::encode($blockConfig);
//     file_put_contents($blocksPath.'/'.$blockType.'.json', Json::prettyPrint($blockJson));
// }

return array(
    'addThis' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\AddThis',
        'definitionFile' => $blocksPath . '/addThis.json'
    ),
    'addThisFollow' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\AddThisFollow',
        'definitionFile' => $blocksPath . '/addThisFollow.json'
    ),
    'advancedContact' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\AdvancedContact',
        'definitionFile' => $blocksPath . '/advancedContact.json'
    ),
    'advancedSearchForm' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\AdvancedSearch',
        'definitionFile' => $blocksPath . '/advancedSearchForm.json'
    ),
    'audio' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Audio',
        'definitionFile' => $blocksPath . '/audio.json'
    ),
    'authentication' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Authentication',
        'definitionFile' => $blocksPath . '/authentication.json'
    ),
    'breadcrumb' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Breadcrumbs',
        'definitionFile' => $blocksPath . '/breadcrumb.json'
    ),
    'calendar' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Calendar',
        'definitionFile' => $blocksPath . '/calendar.json'
    ),
    'carrousel' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Carrousel',
        'definitionFile' => $blocksPath . '/carrousel.json'
    ),
    'contact' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Contact',
        'definitionFile' => $blocksPath . '/contact.json'
    ),
    'contentDetail' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ContentSingle',
        'definitionFile' => $blocksPath . '/contentDetail.json'
    ),
    'contentList' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ContentList',
        'definitionFile' => $blocksPath . '/contentList.json'
    ),
    'damList' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\DamList',
        'definitionFile' => $blocksPath . '/damList.json'
    ),
    'externalMedia' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\EmbeddedMedia',
        'definitionFile' => $blocksPath . '/externalMedia.json'
    ),
    'flickrGallery' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\FlickrGallery',
        'definitionFile' => $blocksPath . '/flickrGallery.json'
    ),
    'geoSearchResults' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\GeoSearch',
        'definitionFile' => $blocksPath . '/geoSearchResults.json'
    ),
    'image' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Image',
        'definitionFile' => $blocksPath . '/image.json'
    ),
    'imageGallery' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ImageGallery',
        'definitionFile' => $blocksPath . '/imageGallery.json'
    ),
    'imageMap' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ImageMap',
        'definitionFile' => $blocksPath . '/imageMap.json'
    ),
    'languageMenu' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\LanguageMenu',
        'definitionFile' => $blocksPath . '/languageMenu.json'
    ),
    'mailingList' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\MailingList',
        'definitionFile' => $blocksPath . '/mailingList.json'
    ),
    'navigation' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\NavBar',
        'definitionFile' => $blocksPath . '/navigation.json'
    ),
    'protectedResource' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ProtectedResource',
        'definitionFile' => $blocksPath . '/protectedResource.json'
    ),
    'resource' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Resource',
        'definitionFile' => $blocksPath . '/resource.json'
    ),
    'richText' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\RichText',
        'definitionFile' => $blocksPath . '/richText.json'
    ),
    'searchForm' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\SearchForm',
        'definitionFile' => $blocksPath . '/searchForm.json'
    ),
    'searchResults' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Search',
        'definitionFile' => $blocksPath . '/searchResults.json'
    ),
    'simpleText' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\SimpleText',
        'definitionFile' => $blocksPath . '/simpleText.json'
    ),
    'sitemap' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Sitemap',
        'definitionFile' => $blocksPath . '/sitemap.json'
    ),
    'twig' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Twig',
        'definitionFile' => $blocksPath . '/twig.json'
    ),
    'twitter' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Twitter',
        'definitionFile' => $blocksPath . '/twitter.json'
    ),
    'video' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Video',
        'definitionFile' => $blocksPath . '/video.json'
    ),
    'zendController' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ZendController',
        'definitionFile' => $blocksPath . '/zendController.json'
    )
);