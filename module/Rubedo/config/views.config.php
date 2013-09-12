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
    'rubedo/controller/ext-finder/index' => $boViewsPath . '/ext-finder/index.phtml',
    'rubedo/controller/link-finder/index' => $boViewsPath . '/link-finder/index.phtml',
    'rubedo/controller/login/index' => $boViewsPath . '/login/index.phtml',
    'rubedo/install/controller/index/form' => $installViewPath . '/index/form.phtml',
    'rubedo/install/controller/index/element' => $installViewPath . '/index/element.phtml',
    'rubedo/install/controller/index/fieldset' => $installViewPath . '/index/fieldset.phtml',
    'rubedo/install/controller/index/index' => $installViewPath . '/index/index.phtml',
    'rubedo/install/controller/index/start-wizard' => $installViewPath . '/index/start-wizard.phtml',
    'rubedo/install/controller/index/set-db' => $installViewPath . '/index/set-db.phtml',
    'rubedo/install/controller/index/set-elastic-search' => $installViewPath . '/index/set-elastic-search.phtml',
    'rubedo/install/controller/index/define-languages' => $installViewPath . '/index/define-languages.phtml',
    'rubedo/install/controller/index/set-admin' => $installViewPath . '/index/set-admin.phtml',
    'rubedo/install/controller/index/set-db-contents' => $installViewPath . '/index/set-db-contents.phtml',
    'rubedo/install/controller/index/set-local-domains' => $installViewPath . '/index/set-local-domains.phtml',
    'rubedo/install/controller/index/set-mailer' => $installViewPath . '/index/set-mailer.phtml',
    'rubedo/install/controller/index/set-php-settings' => $installViewPath . '/index/set-php-settings.phtml'
);
