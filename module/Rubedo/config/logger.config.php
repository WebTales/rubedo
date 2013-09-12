<?php
return array(
    'handlers' => array(
        'ChromePHPHandler' => array(
            'class' => 'Monolog\\Handler\\ChromePHPHandler',
            'level' => 'ERROR',
            'enable' => false
        ),
        'FirePHPHandler' => array(
            'class' => 'Monolog\\Handler\\FirePHPHandler',
            'level' => 'ERROR',
            'enable' => false
        ),
        'MongoDBHandler' => array(
            'class' => 'Monolog\\Handler\\MongoDBHandler',
            'collection' => 'Logs',
            'database' => 'inherit',
            'level' => 'ERROR',
            'enable' => false
        ),
        'StreamHandler' => array(
            'class' => 'Monolog\\Handler\\StreamHandler',
            'dirPath' => APPLICATION_PATH . '/log',
            'level' => 'ERROR',
            'enable' => false
        )
    ),
    'applicationLevel'=>'INFO'
);