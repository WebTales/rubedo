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

use Monolog\Handler\MongoDBHandler;

/**
 * Logger Service for security Issues
 *
 * Use monolog
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ApplicationLogger extends Logger
{

    protected static $logName = 'rubedo';

    protected static $logCollection = 'RubedoLog';

    public function __construct()
    {
        $this->logger = new monologger(static::$logName);
        $config = $this->getConfig();
        $level = isset($config['applicationLevel']) ? $config['applicationLevel'] : 'INFO';
        $levels = $this->logger->getLevels();
        $level = $levels[$level];
        
        $mongoClient = Manager::getService('MongoDataAccess')->getAdapter(DataAccess::getDefaultMongo());
        $handler = new MongoDBHandler($mongoClient, DataAccess::getDefaultDb(), static::$logCollection, $level);
        $this->logger->pushHandler($handler);
    }
}
