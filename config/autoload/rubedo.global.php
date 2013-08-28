<?php
$config = array();

$sessionLifeTime = 3600;

$config['session'] = array(
    'remember_me_seconds' => $sessionLifeTime,
    'use_cookies' => true,
    'cookie_httponly' => false,
    //'cookieLifetime' => $sessionLifeTime,
    'gc_maxlifetime' => $sessionLifeTime,
    'name' => 'rubedo',
    'cookie_httponly'=>true
);

$config['datastream'] = array();

$config['datastream']['mongo'] = array(
    'server' => 'localhost',
    'port' => '27017',
    'db' => 'rubedo',
    'login' => '',
    'password' => ''
);

$config['elastic'] = array(
    "host" => "localhost",
    "port" => "9200",
    "contentIndex" => "contents",
    "damIndex" => "dam"
);

return $config;