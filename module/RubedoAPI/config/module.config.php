<?php
return array(
    'router' => array(
        'routes' => array(
            // route for different frontoffice controllers
            'api' =>array (
                'type' => 'RubedoAPI\\Router\\ApiRouter',
                'options' => array (
                    'defaults' =>
                        array (
                            '__NAMESPACE__' => 'RubedoAPI\\Frontoffice\\Controller',
                            'controller' => 'Api',
                            'action' => 'index',
                        ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'RubedoAPI\\Frontoffice\\Controller\\Api' => 'RubedoAPI\\Frontoffice\\Controller\\ApiController',
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
        ),
    ),
);
