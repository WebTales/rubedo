<?php
use Monolog\Logger;

return array(
    'handlers' => array(
        'ChromePHPHandler' => array(
            'class' => 'Monolog\\Handler\\ChromePHPHandler',
        ),
        'FirePHPHandler' => array(
            'class' => 'Monolog\\Handler\\FirePHPHandler',
        ),
        'MongoDBHandler' => array(
            'class' => 'Monolog\\Handler\\MongoDBHandler',
            'collection' => 'Logs',
            'database' => 'inherit',
        ),
        'StreamHandler' => array(
            'class' => 'Monolog\\Handler\\StreamHandler',
            'dirPath' => APPLICATION_PATH . '/log',
        )
    ),
    'enableHandler' => array(
        'ChromePHPHandler' => 0,
        'FirePHPHandler' => 0,
        'MongoDBHandler' => 0,
        'StreamHandler' => 0
    ),
    'errorLevel' => Logger::ERROR,
    'applicationLevel' => Logger::INFO
);