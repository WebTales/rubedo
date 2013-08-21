<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
$boViewsPath = realpath(__DIR__ . '/../src/Rubedo/Backoffice/views/scripts');

$serviceArray = include(__DIR__.'/services.config.php');
$controllerArray = include(__DIR__.'/controllers.config.php');
$viewArray = include(__DIR__.'/views.config.php');
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
        'invokables' => $controllerArray
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => $viewArray,
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
