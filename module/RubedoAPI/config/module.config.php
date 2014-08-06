<?php
return array(
    'router' => array(
        'routes' => array(
            // route for different frontoffice controllers
            'api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/:version/:ressource',
                    'defaults' => array(
                        '__NAMESPACE__' => 'RubedoAPI\\Frontoffice\\Controller',
                        'controller' => 'Api',
                        'action' => 'index',
                    ),
                    'constraints' => array(
                        'version' => 'v\d+',
                    ),
                ),
                'may_terminate' => true,
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'RubedoAPI\\Frontoffice\\Controller\\Api' => 'RubedoAPI\\Frontoffice\\Controller\\ApiController',
        ),
    ),
);
