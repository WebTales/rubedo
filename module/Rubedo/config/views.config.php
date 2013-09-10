<?php
$boViewsPath = realpath(__DIR__ . '/../src/Rubedo/Backoffice/views/scripts');
$installViewPath = realpath(__DIR__ . '/../src/Rubedo/Install/views/scripts');
return array(
    'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
    'layout/install' => $installViewPath . '/install-layout.phtml',
    'error/404' => __DIR__ . '/../view/error/404.phtml',
    'error/index' => __DIR__ . '/../view/error/index.phtml',
    'rubedo/controller/index/index' => $boViewsPath . '/index/index.phtml',
    'rubedo/controller/content-contributor/index' => $boViewsPath . '/content-contributor/index.phtml',
    'rubedo/controller/login/index' => $boViewsPath . '/login/index.phtml',
    'rubedo/install/controller/index/form' => $installViewPath.'/index/form.phtml',
    'rubedo/install/controller/index/index' => $installViewPath.'/index/index.phtml',
    'rubedo/install/controller/index/start-wizard' => $installViewPath.'/index/start-wizard.phtml',
    'rubedo/install/controller/index/set-db' => $installViewPath.'/index/set-db.phtml',
    'rubedo/install/controller/index/set-elastic-search' => $installViewPath.'/index/set-elastic-search.phtml'
);