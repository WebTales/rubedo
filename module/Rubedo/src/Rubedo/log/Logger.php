<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Log;

use Monolog\Logger as monologger;
use Rubedo\Exceptions\Server;
use Rubedo\Services\Manager;
use Monolog\Handler\StreamHandler;
use Rubedo\Mongo\DataAccess;

/**
 * Logger Service
 *
 * Use monolog
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Logger
{
    protected static $logName = 'Rubedo';

    protected $logger = null;

    protected static $config;
    

    public function __construct()
    {
        $this->logger = new monologger(static::$logName);
        $config = $this->getConfig();
        $levels = $this->logger->getLevels();
        if (isset($config['handlers'])) {
            foreach ($config['handlers'] as $handler) {
                if (! $handler['enable']) {
                    continue;
                }
                $className = $handler['class'];
                if (isset($handler['level'])) {
                    $level = $levels[$handler['level']];
                } else {
                    $level = null;
                }
                switch ($className) {
                    case 'Monolog\\Handler\\StreamHandler':
                        if(isset($handler['dirPath'])){
                            $handler['path'] = $handler['dirPath'].'/'.static::$logName.'.log';
                        }
                        $handler = new $className($handler['path'], $level);
                        break;
                    case 'Monolog\\Handler\\MongoDBHandler':
                        if ($handler['database'] == 'inherit') {
                            $handler['database'] = DataAccess::getDefaultDb();
                        }
                        if (! isset($handler['connectionPath'])) {
                            $handler['connectionPath'] = DataAccess::getDefaultMongo();
                        }
                        $mongoClient = Manager::getService('MongoDataAccess')->getAdapter($handler['connectionPath']);
                        $handler = new $className($mongoClient, $handler['database'], $handler['collection'], $level);
                        break;
                    default:
                        $handler = new $className($level);
                        break;
                }
                $this->logger->pushHandler($handler);
            }
        }
    }

    protected function getConfig()
    {
        if (! isset(static::$config)) {
            $appConfig = Manager::getService('config');
            static::$config = $appConfig['logger'];
        }
        return static::$config;
    }

    public function __call($function, array $args)
    {
        $callBack = array(
            $this->logger,
            $function
        );
        
        if (! is_callable($callBack)) {
            throw new Server('Method not found');
        }
        return call_user_func_array($callBack, $args);
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
