<?php
// $result = array();
// $bocontrollerDirectory = realpath(APPLICATION_PATH . '/module/Rubedo/src/Rubedo/Backoffice/Controller');

// $iterator = new DirectoryIterator($bocontrollerDirectory);
// foreach ($iterator as $item) {
//     if ($item->isDot()) {
//         continue;
//     }
//     $fileName = $item->getFilename();
//     $controllerName = str_replace('Controller.php', '', $fileName);
//     $result['Rubedo\Backoffice\Controller\\' . $controllerName] = 'Rubedo\Backoffice\Controller\\' . $controllerName . 'Controller';
// }

// ksort($result);
// var_export($result);
// die();
// return $result;
return array(
    'Rubedo\\Backoffice\\Controller\\Acl' => 'Rubedo\\Backoffice\\Controller\\AclController',
    'Rubedo\\Backoffice\\Controller\\Cache' => 'Rubedo\\Backoffice\\Controller\\CacheController',
    'Rubedo\\Backoffice\\Controller\\ContentContributor' => 'Rubedo\\Backoffice\\Controller\\ContentContributorController',
    'Rubedo\\Backoffice\\Controller\\ContentTypes' => 'Rubedo\\Backoffice\\Controller\\ContentTypesController',
    'Rubedo\\Backoffice\\Controller\\Contents' => 'Rubedo\\Backoffice\\Controller\\ContentsController',
    'Rubedo\\Backoffice\\Controller\\CurrentUser' => 'Rubedo\\Backoffice\\Controller\\CurrentUserController',
    'Rubedo\\Backoffice\\Controller\\CustomThemes' => 'Rubedo\\Backoffice\\Controller\\CustomThemesController',
    'Rubedo\\Backoffice\\Controller\\Dam' => 'Rubedo\\Backoffice\\Controller\\DamController',
    'Rubedo\\Backoffice\\Controller\\DamTypes' => 'Rubedo\\Backoffice\\Controller\\DamTypesController',
    'Rubedo\\Backoffice\\Controller\\Delegations' => 'Rubedo\\Backoffice\\Controller\\DelegationsController',
    'Rubedo\\Backoffice\\Controller\\Directories' => 'Rubedo\\Backoffice\\Controller\\DirectoriesController',
    'Rubedo\\Backoffice\\Controller\\ElasticIndexer' => 'Rubedo\\Backoffice\\Controller\\ElasticIndexerController',
    'Rubedo\\Backoffice\\Controller\\ElasticSearch' => 'Rubedo\\Backoffice\\Controller\\ElasticSearchController',
    'Rubedo\\Backoffice\\Controller\\ElasticSearchContent' => 'Rubedo\\Backoffice\\Controller\\ElasticSearchContentController',
    'Rubedo\\Backoffice\\Controller\\ElasticSearchDam' => 'Rubedo\\Backoffice\\Controller\\ElasticSearchDamController',
    'Rubedo\\Backoffice\\Controller\\ElasticSearchGeo' => 'Rubedo\\Backoffice\\Controller\\ElasticSearchGeoController',
    'Rubedo\\Backoffice\\Controller\\ExtFinder' => 'Rubedo\\Backoffice\\Controller\\ExtFinderController',
    'Rubedo\\Backoffice\\Controller\\FieldTypes' => 'Rubedo\\Backoffice\\Controller\\FieldTypesController',
    'Rubedo\\Backoffice\\Controller\\File' => 'Rubedo\\Backoffice\\Controller\\FileController',
    'Rubedo\\Backoffice\\Controller\\FoThemes' => 'Rubedo\\Backoffice\\Controller\\FoThemesController',
    'Rubedo\\Backoffice\\Controller\\Forms' => 'Rubedo\\Backoffice\\Controller\\FormsController',
    'Rubedo\\Backoffice\\Controller\\GenericCleaning' => 'Rubedo\\Backoffice\\Controller\\GenericCleaningController',
    'Rubedo\\Backoffice\\Controller\\Groups' => 'Rubedo\\Backoffice\\Controller\\GroupsController',
    'Rubedo\\Backoffice\\Controller\\Icons' => 'Rubedo\\Backoffice\\Controller\\IconsController',
    'Rubedo\\Backoffice\\Controller\\Image' => 'Rubedo\\Backoffice\\Controller\\ImageController',
    'Rubedo\\Backoffice\\Controller\\Import' => 'Rubedo\\Backoffice\\Controller\\ImportController',
    'Rubedo\\Backoffice\\Controller\\Index' => 'Rubedo\\Backoffice\\Controller\\IndexController',
    'Rubedo\\Backoffice\\Controller\\Languages' => 'Rubedo\\Backoffice\\Controller\\LanguagesController',
    'Rubedo\\Backoffice\\Controller\\LinkFinder' => 'Rubedo\\Backoffice\\Controller\\LinkFinderController',
    'Rubedo\\Backoffice\\Controller\\Localisation' => 'Rubedo\\Backoffice\\Controller\\LocalisationController',
    'Rubedo\\Backoffice\\Controller\\Login' => 'Rubedo\\Backoffice\\Controller\\LoginController',
    'Rubedo\\Backoffice\\Controller\\Logout' => 'Rubedo\\Backoffice\\Controller\\LogoutController',
    'Rubedo\\Backoffice\\Controller\\MailingLists' => 'Rubedo\\Backoffice\\Controller\\MailingListsController',
    'Rubedo\\Backoffice\\Controller\\Masks' => 'Rubedo\\Backoffice\\Controller\\MasksController',
    'Rubedo\\Backoffice\\Controller\\NestedContents' => 'Rubedo\\Backoffice\\Controller\\NestedContentsController',
    'Rubedo\\Backoffice\\Controller\\Pages' => 'Rubedo\\Backoffice\\Controller\\PagesController',
    'Rubedo\\Backoffice\\Controller\\PersonalPrefs' => 'Rubedo\\Backoffice\\Controller\\PersonalPrefsController',
    'Rubedo\\Backoffice\\Controller\\Queries' => 'Rubedo\\Backoffice\\Controller\\QueriesController',
    'Rubedo\\Backoffice\\Controller\\ReusableElements' => 'Rubedo\\Backoffice\\Controller\\ReusableElementsController',
    'Rubedo\\Backoffice\\Controller\\Roles' => 'Rubedo\\Backoffice\\Controller\\RolesController',
    'Rubedo\\Backoffice\\Controller\\RubedoVersion' => 'Rubedo\\Backoffice\\Controller\\RubedoVersionController',
    'Rubedo\\Backoffice\\Controller\\Sites' => 'Rubedo\\Backoffice\\Controller\\SitesController',
    'Rubedo\\Backoffice\\Controller\\Taxonomy' => 'Rubedo\\Backoffice\\Controller\\TaxonomyController',
    'Rubedo\\Backoffice\\Controller\\TaxonomyTerms' => 'Rubedo\\Backoffice\\Controller\\TaxonomyTermsController',
    'Rubedo\\Backoffice\\Controller\\Themes' => 'Rubedo\\Backoffice\\Controller\\ThemesController',
    'Rubedo\\Backoffice\\Controller\\Update' => 'Rubedo\\Backoffice\\Controller\\UpdateController',
    'Rubedo\\Backoffice\\Controller\\Users' => 'Rubedo\\Backoffice\\Controller\\UsersController',
    'Rubedo\\Backoffice\\Controller\\Versioning' => 'Rubedo\\Backoffice\\Controller\\VersioningController',
    'Rubedo\\Backoffice\\Controller\\Wallpapers' => 'Rubedo\\Backoffice\\Controller\\WallpapersController',
    'Rubedo\\Backoffice\\Controller\\Workspaces' => 'Rubedo\\Backoffice\\Controller\\WorkspacesController',
    'Rubedo\\Backoffice\\Controller\\XhrAuthentication' => 'Rubedo\\Backoffice\\Controller\\XhrAuthenticationController',
    'Rubedo\\Backoffice\\Controller\\XhrGetMongoId' => 'Rubedo\\Backoffice\\Controller\\XhrGetMongoIdController',
    'Rubedo\\Backoffice\\Controller\\XhrGetPageUrl' => 'Rubedo\\Backoffice\\Controller\\XhrGetPageUrlController',
    'Rubedo\\Frontoffice\\Controller\\Image' => 'Rubedo\\Frontoffice\\Controller\\ImageController',
    'Rubedo\\Frontoffice\\Controller\\Error'=>'Rubedo\\Frontoffice\\Controller\\ErrorController'
);