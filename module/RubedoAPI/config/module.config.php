<?php
return array(
    'router' => array(
        'routes' => array(
            'rubedo-api.rest.application-log' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/application-log[/:application_log_id]',
                    'defaults' => array(
                        'controller' => 'RubedoAPI\\V1\\Rest\\ApplicationLog\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'rubedo-api.rest.application-log',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'RubedoAPI\\V1\\Rest\\ApplicationLog\\ApplicationLogResource' => 'RubedoAPI\\V1\\Rest\\ApplicationLog\\ApplicationLogResourceFactory',
        ),
    ),
    'zf-rest' => array(
        'RubedoAPI\\V1\\Rest\\ApplicationLog\\Controller' => array(
            'listener' => 'RubedoAPI\\V1\\Rest\\ApplicationLog\\ApplicationLogResource',
            'route_name' => 'rubedo-api.rest.application-log',
            'route_identifier_name' => 'application_log_id',
            'collection_name' => 'application_log',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'RubedoAPI\\V1\\Rest\\ApplicationLog\\ApplicationLogEntity',
            'collection_class' => 'RubedoAPI\\V1\\Rest\\ApplicationLog\\ApplicationLogCollection',
            'service_name' => 'ApplicationLog',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'RubedoAPI\\V1\\Rest\\ApplicationLog\\Controller' => 'HalJson',
        ),
        'accept_whitelist' => array(
            'RubedoAPI\\V1\\Rest\\ApplicationLog\\Controller' => array(
                0 => 'application/vnd.rubedo-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content_type_whitelist' => array(
            'RubedoAPI\\V1\\Rest\\ApplicationLog\\Controller' => array(
                0 => 'application/vnd.rubedo-api.v1+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'RubedoAPI\\V1\\Rest\\ApplicationLog\\ApplicationLogEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'rubedo-api.rest.application-log',
                'route_identifier_name' => 'application_log_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'RubedoAPI\\V1\\Rest\\ApplicationLog\\ApplicationLogCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'rubedo-api.rest.application-log',
                'route_identifier_name' => 'application_log_id',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-content-validation' => array(
        'RubedoAPI\\V1\\Rest\\ApplicationLog\\Controller' => array(
            'input_filter' => 'RubedoAPI\\V1\\Rest\\ApplicationLog\\Validator',
        ),
    ),
    'input_filter_specs' => array(
        'RubedoAPI\\V1\\Rest\\ApplicationLog\\Validator' => array(
            0 => array(
                'name' => 'name',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            1 => array(
                'name' => 'toto',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
        ),
    ),
    'zf-mvc-auth' => array(
        'authorization' => array(
            'RubedoAPI\\V1\\Rest\\ApplicationLog\\Controller' => array(
                'entity' => array(
                    'GET' => true,
                    'POST' => false,
                    'PATCH' => false,
                    'PUT' => false,
                    'DELETE' => false,
                ),
                'collection' => array(
                    'GET' => false,
                    'POST' => false,
                    'PATCH' => false,
                    'PUT' => false,
                    'DELETE' => false,
                ),
            ),
        ),
    ),
);
