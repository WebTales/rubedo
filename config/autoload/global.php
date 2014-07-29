<?php
return array(
    'router' => array(
        'routes' => array(
            'oauth' => array(
                'options' => array(
                    'route' => '/oauth',
                ),
            ),
        ),
    ),
    'zf-oauth2' => array(
        'storage' => 'RubedoAPI\\Storage\\MongoStorage',
        'allow_implicit' => true,
    ),
);
