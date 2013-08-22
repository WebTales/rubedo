<?php
$config = array();

$config['session'] = array(
    'remember_me_seconds' => 300,
    'use_cookies' => true,
    'cookie_httponly' => false,
    // 'cookieLifetime' => 300,
    'name' => 'rubedo'
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