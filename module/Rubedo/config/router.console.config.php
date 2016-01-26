<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2014, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

/**
 * Configure all routes for Rubedo
 */
return array(
    'routes' => array(
        'clear-cache' => array(
            'options' => array(
                'route'    => 'cache clear [config|files|mongo|url|api]:name',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Cache',
                    'action'     => 'clear'
                )
            )
        ),
        'count-cache' => array(
            'options' => array(
                'route'    => 'cache count',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Cache',
                    'action'     => 'count'
                )
            )
        ),
        'index' => array(
            'options' => array(
                'route'    => 'index [<type>]',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Search',
                    'action'     => 'index'
                )
            )
        ),
        'configdb' => array(
            'options' => array(
                'route'    => 'config setdb --server= --port= --db= [--replicaSetName=] [--adminLogin=] [--adminPassword=] [--login=] [--password=] [--readPreference=]',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setdb'
                )
            )
        ),
        'configes' => array(
            'options' => array(
                'route'    => 'config setes --host= --port= --contentIndex= --damIndex= --userIndex=',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setes'
                )
            )
        ),
        'configlang' => array(
            'options' => array(
                'route'    => 'config setlang <lang>',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setlang'
                )
            )
        ),
        'configreset' => array(
            'options' => array(
                'route'    => 'config reset',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'reset'
                )
            )
        ),
        'configweb' => array(
            'options' => array(
                'route'    => 'config setweb --servers=',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setweb'
                )
            )
        ),
        'configinitdb' => array(
            'options' => array(
                'route'    => 'config initdb',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'initdb'
                )
            )
        ),
        'configfinished' => array(
            'options' => array(
                'route'    => 'config setfinished',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setfinished'
                )
            )
        ),
        'configadmin' => array(
            'options' => array(
                'route'    => 'config setadmin --name=  --email= --login= [--password=] [--salt=] [--hashedPassword=]',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setadmin'
                )
            )
        ),
        'configsetdefault' => array(
            'options' => array(
                'route'    => 'config setdefault',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setdefault'
                )
            )
        ),
        'configcreatesite' => array(
            'options' => array(
                'route'    => 'config createsite --domain= --lang= [--theme=]',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'createsite'
                )
            )
        ),
        'configcgetfull' => array(
            'options' => array(
                'route'    => 'config getfull',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'getfull'
                )
            )
        ),
        'configcsetfull' => array(
            'options' => array(
                'route'    => 'config setfull --conf=',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setfull'
                )
            )
        ),
        'configgetdb' => array(
            'options' => array(
                'route'    => 'config getdb',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'getdb'
                )
            )
        ),
        'configgetes' => array(
            'options' => array(
                'route'    => 'config getes',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'getes'
                )
            )
        ),
        'configgetweb' => array(
            'options' => array(
                'route'    => 'config getweb',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'getweb'
                )
            )
        ),
        'configgetmail' => array(
            'options' => array(
                'route'    => 'config getmail',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'getmail'
                )
            )
        ),
        'configsetmail' => array(
            'options' => array(
                'route'    => 'config setmail --server= --port= [--ssl] --username= --password=',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setmail'
                )
            )
        ),
        'configgetrubedoconfig' => array(
            'options' => array(
                'route'    => 'config getrubedoconfig',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'getrubedoconfig'
                )
            )
        ),
        'configsetrubedoconfig' => array(
            'options' => array(
                'route'    => 'config setrubedoconfig [--minify] [--cachePage] [--apiCache] [--useCdn] [--extDebug] [--addECommerce] [--activateMagic] [--defaultBackofficeHost=] [--isBackofficeSSL] [--enableEmailNotification] [--fromEmailNotification=]',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'setrubedoconfig'
                )
            )
        ),
        'configsaveconfigtodb' => array(
            'options' => array(
                'route'    => 'config saveconfigtodb',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'saveconfigtodb'
                )
            )
        ),
        'configrestoreconfigfromdb' => array(
            'options' => array(
                'route'    => 'config restoreconfigfromdb',
                'defaults' => array(
                    'controller' => 'Rubedo\Console\Controller\Config',
                    'action'     => 'restoreconfigfromdb'
                )
            )
        ),
    )
);
