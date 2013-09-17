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
use Monolog\Logger as monologger;
use Rubedo\Services\Manager;
use Rubedo\Mongo\DataAccess;
use Zend\EventManager\EventInterface;
use Rubedo\Collection\AbstractCollection;
use Rubedo\Collection\WorkflowAbstractCollection;
use Rubedo\User\Authentication;

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

    protected static $logCollection = 'ApplicationLog';

    public function __construct()
    {
        $this->logger = new monologger(static::$logName);
        $config = $this->getConfig();
        $level = monologger::INFO;
        
        $mongoClient = Manager::getService('MongoDataAccess')->getAdapter(DataAccess::getDefaultMongo());
        $handler = new MongoDBHandler($mongoClient, DataAccess::getDefaultDb(), static::$logCollection, $level);
        $this->logger->pushHandler($handler);
    }

    /**
     * listener to log writing on collections
     *
     * @param EventInterface $e            
     */
    public function logCollectionEvent(EventInterface $e)
    {
        $params = $e->getParams();
        
        $collection = $e->getTarget()->getCollectionName();
        $userSummary = Manager::getService('CurrentUser')->getCurrentUserSummary();
        if (! $userSummary['fullName']) {
            // do not log anonymous writing
            return;
        }
        switch ($e->getName()) {
            case AbstractCollection::POST_CREATE_COLLECTION:
                $action = 'Create';
                break;
            case AbstractCollection::POST_UPDATE_COLLECTION:
                $action = 'Update';
                break;
            case AbstractCollection::POST_DELETE_COLLECTION:
                $action = 'Delete';
                break;
            case WorkflowAbstractCollection::POST_PUBLISH_COLLECTION:
                $action = 'Publish';
                break;
            default:
                $action = $e->getName();
                break;
        }
        $context = array(
            'type' => 'collection',
            'collection' => $collection,
            'user' => $userSummary,
            'event' => $e->getName(),
            'data' => $params['data']
        );
        $this->info($action . ' on ' . $collection . ' by ' . $userSummary['fullName'], $context);
    }

    public function logAuthenticationEvent(EventInterface $e)
    {
        $serverParams = Manager::getService('Application')->getRequest()->getServer();
        $context = array(
            'remote_ip' => $serverParams->get('X-Forwarded-For', $serverParams->get('REMOTE_ADDR')),
            'uri' => Manager::getService('Application')->getRequest()
                ->getUri()
                ->toString(),
            'type'=> 'authentication',
            'event' => $e->getName(),            
        );
        
        $userSummary = Manager::getService('CurrentUser')->getCurrentUserSummary();
        
        switch ($e->getName()) {
            case Authentication::FAIL:
                $message = 'Failed authentication';
                $params = $e->getParams();
                $login = $params['login'];
                $level = \Monolog\Logger::WARNING;
                $context['error']=$params['error'];
                break;
            case Authentication::SUCCESS:
                $message = 'Successful authentication';
                $action = 'Update';
                $currentUser = Manager::getService('CurrentUser')->getCurrentUserSummary();
                $login = $currentUser['login'];
                $level = \Monolog\Logger::INFO;
                break;
        }
        $context['login'] = $login;
        $this->addRecord($level,$message, $context);
    }
}
