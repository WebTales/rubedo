<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
$boViewsPath = realpath(__DIR__ . '/../src/Rubedo/Backoffice/views/scripts');

$serviceArray = array(
    'MongoDataAccess' => array(
        'class' => 'Rubedo\\Mongo\\DataAccess'
    ),
    'MongoWorkflowDataAccess' => array(
        'class' => 'Rubedo\\Mongo\\WorkflowDataAccess'
    ),
    'MongoFileAccess' => array(
        'class' => 'Rubedo\\Mongo\\FileAccess'
    ),
    'ElasticDataSearch' => array(
        'class' => 'Rubedo\\Elastic\\DataSearch'
    ),
    'ElasticDataIndex' => array(
        'class' => 'Rubedo\\Elastic\\DataIndex'
    ),
    'CurrentUser' => array(
        'class' => 'Rubedo\\User\\CurrentUser'
    ),
    'Session' => array(
        'class' => 'Rubedo\\User\\Session'
    ),
    'Authentication' => array(
        'class' => 'Rubedo\\User\\Authentication'
    ),
    'CurrentTime' => array(
        'class' => 'Rubedo\\Time\\CurrentTime'
    ),
    'Date' => array(
        'class' => 'Rubedo\\Time\\Date'
    ),
    'Url' => array(
        'class' => 'Rubedo\\Router\\Url'
    ),
    'FrontOfficeTemplates' => array(
        'class' => 'Rubedo\\Templates\\FrontOfficeTemplates'
    ),
    'Acl' => array(
        'class' => 'Rubedo\\Security\\Acl'
    ),
    'Hash' => array(
        'class' => 'Rubedo\\Security\\Hash'
    ),
    'HtmlCleaner' => array(
        'class' => 'Rubedo\\Security\\HtmlPurifier'
    ),
    'PageContent' => array(
        'class' => 'Rubedo\\Content\\Page'
    ),
    'Users' => array(
        'class' => 'Rubedo\\Collection\\Users'
    ),
    'UrlCache' => array(
        'class' => 'Rubedo\\Collection\\UrlCache'
    ),
    'Masks' => array(
        'class' => 'Rubedo\\Collection\\Masks'
    ),
    'ReusableElements' => array(
        'class' => 'Rubedo\\Collection\\ReusableElements'
    ),
    'Blocks' => array(
        'class' => 'Rubedo\\Collection\\Blocks'
    ),
    'Contents' => array(
        'class' => 'Rubedo\\Collection\\Contents'
    ),
    'ContentTypes' => array(
        'class' => 'Rubedo\\Collection\\ContentTypes'
    ),
    'Delegations' => array(
        'class' => 'Rubedo\\Collection\\Delegations'
    ),
    'Forms' => array(
        'class' => 'Rubedo\\Collection\\Forms'
    ),
    'FormsResponses' => array(
        'class' => 'Rubedo\\Collection\\FormsResponses'
    ),
    'FieldTypes' => array(
        'class' => 'Rubedo\\Collection\\FieldTypes'
    ),
    'Groups' => array(
        'class' => 'Rubedo\\Collection\\Groups'
    ),
    'Icons' => array(
        'class' => 'Rubedo\\Collection\\Icons'
    ),
    
    'PersonalPrefs' => array(
        'class' => 'Rubedo\\Collection\\PersonalPrefs'
    ),
    'Sites' => array(
        'class' => 'Rubedo\\Collection\\Sites'
    ),
    'Taxonomy' => array(
        'class' => 'Rubedo\\Collection\\Taxonomy'
    ),
    'TaxonomyTerms' => array(
        'class' => 'Rubedo\\Collection\\TaxonomyTerms'
    ),
    'Themes' => array(
        'class' => 'Rubedo\\Collection\\Themes'
    ),
    'TinyUrl' => array(
        'class' => 'Rubedo\\Collection\\TinyUrl'
    ),
    'Wallpapers' => array(
        'class' => 'Rubedo\\Collection\\Wallpapers'
    ),
    'NestedContents' => array(
        'class' => 'Rubedo\\Collection\\NestedContents'
    ),
    'Pages' => array(
        'class' => 'Rubedo\\Collection\\Pages'
    ),
    'Versioning' => array(
        'class' => 'Rubedo\\Collection\\Versioning'
    ),
    'Images' => array(
        'class' => 'Rubedo\\Collection\\Images'
    ),
    'Files' => array(
        'class' => 'Rubedo\\Collection\\Files'
    ),
    'Cache' => array(
        'class' => 'Rubedo\\Collection\\Cache'
    ),
    'Queries' => array(
        'class' => 'Rubedo\\Collection\\Queries'
    ),
    'Dam' => array(
        'class' => 'Rubedo\\Collection\\Dam'
    ),
    'DamTypes' => array(
        'class' => 'Rubedo\\Collection\\DamTypes'
    ),
    'Workspaces' => array(
        'class' => 'Rubedo\\Collection\\Workspaces'
    ),
    'Mailer' => array(
        'class' => 'Rubedo\\Mail\\Mailer'
    ),
    'Notification' => array(
        'class' => 'Rubedo\\Mail\\Notification'
    ),
    'MailingList' => array(
        'class' => 'Rubedo\\Collection\\MailingList'
    ),
    'Localisation' => array(
        'class' => 'Rubedo\\Collection\\Localisation'
    ),
    'RubedoVersion' => array(
        'class' => 'Rubedo\\Collection\\RubedoVersion'
    ),
    'Directories' => array(
        'class' => 'Rubedo\\Collection\\Directories'
    ),
    'Translate' => array(
        'class' => 'Rubedo\\Internationalization\\Translate'
    ),
    'CurrentLocalization' => array(
        'class' => 'Rubedo\\Internationalization\\Current'
    ),
    'Languages' => array(
        'class' => 'Rubedo\\Collection\\Languages'
    ),
    'CustomThemes' => array(
        'class' => 'Rubedo\\Collection\\CustomThemes'
    )
);

$serviceMapArray = array();

foreach ($serviceArray as $key => $value) {
    $serviceMapArray[$key] = $value['class'];
    $serviceSharedMapArray[$key] = false;
}

$config = array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Rubedo\Frontoffice\Controller\Index',
                        'action' => 'index'
                    )
                )
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/backoffice',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Rubedo\Backoffice\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            '__NAMESPACE__' => 'Rubedo\Backoffice\Controller',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Rubedo\Backoffice\Controller\Index' => 'Rubedo\Backoffice\Controller\IndexController',
            'Rubedo\Backoffice\Controller\Login' => 'Rubedo\Backoffice\Controller\LoginController',
            'Rubedo\Backoffice\Controller\Logout' => 'Rubedo\Backoffice\Controller\LogoutController',
            'Rubedo\Backoffice\Controller\XhrAuthentication' => 'Rubedo\Backoffice\Controller\XhrAuthenticationController',
            'Rubedo\Backoffice\Controller\Icons'=>'Rubedo\Backoffice\Controller\IconsController',
            'Rubedo\Backoffice\Controller\Acl'=>'Rubedo\Backoffice\Controller\AclController',
            'Rubedo\Backoffice\Controller\CurrentUser'=>'Rubedo\Backoffice\Controller\CurrentUserController'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'rubedo/controller/index/index' => $boViewsPath . '/index/index.phtml',
            'rubedo/controller/index/index' => $boViewsPath . '/index/index.phtml',
            'rubedo/controller/login/index' => $boViewsPath . '/login/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
    'service_manager' => array(
        'invokables' => $serviceMapArray,
        'shared' => $serviceSharedMapArray
    )
);

return $config;
