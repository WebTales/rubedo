<?php
return [
    'router' => [
        'routes' => [
            // route for different frontoffice controllers
            'api' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/:version/:ressource',
                    'defaults' => [
                        '__NAMESPACE__' => 'RubedoAPI\\Frontoffice\\Controller',
                        'controller' => 'Api',
                        'action' => 'index',
                    ],
                    'constraints' => [
                        'version' => 'v\d+',
                    ],
                ],
                'may_terminate' => true,
            ],
            'oauth' => [
                'options' => [
                    'route'    => '/auth/oauth',
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'RubedoAPI\\Frontoffice\\Controller\\Api' => 'RubedoAPI\\Frontoffice\\Controller\\ApiController',
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'RubedoAPI\\AuthStorage\\MongoAuthImplementation' => 'RubedoAPI\\AuthStorage\\MongoAuthImplementation',
        ],
    ],
];
