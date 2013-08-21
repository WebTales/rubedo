<?php
$boViewsPath = realpath(__DIR__ . '/../src/Rubedo/Backoffice/views/scripts');

return array(
    'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
    'error/404' => __DIR__ . '/../view/error/404.phtml',
    'error/index' => __DIR__ . '/../view/error/index.phtml',
    'rubedo/controller/index/index' => $boViewsPath . '/index/index.phtml',
    'rubedo/controller/index/index' => $boViewsPath . '/index/index.phtml',
    'rubedo/controller/login/index' => $boViewsPath . '/login/index.phtml'
);