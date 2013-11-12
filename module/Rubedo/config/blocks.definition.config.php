<?php
// use Zend\Json\Json;
// use Zend\Debug\Debug;
$blocksPath = realpath(__DIR__ . "/blocks/");

// $globalJsonFile = file_get_contents(APPLICATION_PATH.'/public/components/webtales/rubedo-backoffice-ui/www/resources/localisationfiles/generic/blockTypes.json');

// $globalJson = Json::decode($globalJsonFile,Json::TYPE_ARRAY);
// foreach($globalJson as $blockConfig){
// $blockType = $blockConfig['configBasique']['bType'];
// $blockJson = Json::encode($blockConfig);
// file_put_contents($blocksPath.'/'.$blockType.'.json', Json::prettyPrint($blockJson));
// }

return array(
    'addThis' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\AddThis',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/addThis.json'
    ),
    'addThisFollow' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\AddThisFollow',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/addThisFollow.json'
    ),
//     'advancedContact' => array(
//         'controller' => 'Rubedo\\Blocks\\Controller\\AdvancedContact',
//         'maxlifeTime' => - 1,
//         'definitionFile' => $blocksPath . '/advancedContact.json'
//     ),
    'advancedSearchForm' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\AdvancedSearch',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/advancedSearchForm.json'
    ),
    'audio' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Audio',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/audio.json'
    ),
    'authentication' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Authentication',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/authentication.json'
    ),
    'breadcrumb' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Breadcrumbs',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/breadcrumb.json'
    ),
    'calendar' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Calendar',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/calendar.json'
    ),
    'carrousel' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Carrousel',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/carrousel.json'
    ),
    'contact' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Contact',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/contact.json'
    ),
    'contentDetail' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ContentSingle',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/contentDetail.json'
    ),
    'contentList' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ContentList',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/contentList.json'
    ),
    'damList' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\DamList',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/damList.json'
    ),
    'externalMedia' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\EmbeddedMedia',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/externalMedia.json'
    ),
    'flickrGallery' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\FlickrGallery',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/flickrGallery.json'
    ),
    'geoSearchResults' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\GeoSearch',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/geoSearchResults.json'
    ),
    'image' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Image',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/image.json'
    ),
    'imageGallery' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Gallery',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/imageGallery.json'
    ),
    'imageMap' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ImageMap',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/imageMap.json'
    ),
    'languageMenu' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\LanguageMenu',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/languageMenu.json'
    ),
    'mailingList' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\MailingList',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/mailingList.json'
    ),
    'navigation' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\NavBar',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/navigation.json'
    ),
    'protectedResource' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\ProtectedResource',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/protectedResource.json'
    ),
    'resource' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Resource',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/resource.json'
    ),
    'signUp' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\SignUp',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/signUp.json'
    ),
    'richText' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\RichText',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/richText.json'
    ),
    'searchForm' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\SearchForm',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/searchForm.json'
    ),
    'searchResults' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Search',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/searchResults.json'
    ),
    'directory' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Directory',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/directory.json'
    ),
    'userProfile' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\UserProfile',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/userProfile.json'
    ),
    'simpleText' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Text',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/simpleText.json'
    ),
    'siteMap' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\SiteMap',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/siteMap.json'
    ),
    'twig' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Twig',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/twig.json'
    ),
    'twitter' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Twitter',
        'maxlifeTime' => 60,
        'definitionFile' => $blocksPath . '/twitter.json'
    ),
    'video' => array(
        'controller' => 'Rubedo\\Blocks\\Controller\\Video',
        'maxlifeTime' => 86400,
        'definitionFile' => $blocksPath . '/video.json'
    ),
//     'zendController' => array(
//         'controller' => 'Rubedo\\Blocks\\Controller\\ZendController',
//         'maxlifeTime' => - 1,
//         'definitionFile' => $blocksPath . '/zendController.json'
//     )
);