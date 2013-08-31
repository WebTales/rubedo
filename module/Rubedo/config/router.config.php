<?php
return array(
    'routes' => array(
        'frontoffice' => array(
            'type' => 'Rubedo\Router\FrontofficeRoute',
            'options' => array(
                'route' => '/',
                'defaults' => array(
                    'controller' => 'Rubedo\Frontoffice\Controller\Index',
                    'action' => 'index'
                )
            )
        ),
        'miscfrontoffice' => array(
            'type' => 'Literal',
            'options' => array(
                'route' => '/dam',
                'defaults' => array(
                    '__NAMESPACE__' => 'Rubedo\FrontOffice\Controller',
                    'controller' => 'Dam',
                    'action' => 'index'
                )
            ),
            'may_terminate' => true,
            'child_routes' => array(
                'default' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/[:controller[/:action]]',
                        '__NAMESPACE__' => 'Rubedo\Frontoffice\Controller',
                        'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                        ),
                        'defaults' => array()
                    )
                )
            )
        ),
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
        // Backoffice route : prefix by backoffice
        'backoffice' => array(
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
        ),
        // install route : prefix by install
        'install' => array(
            'type' => 'Literal',
            'options' => array(
                'route' => '/install',
                'defaults' => array(
                    '__NAMESPACE__' => 'Rubedo\Install\Controller',
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
                        '__NAMESPACE__' => 'Rubedo\Install\Controller',
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
);