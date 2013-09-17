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



/**
 * Logger Service for security Issues
 *
 * Use monolog
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class SecurityLogger extends Logger
{
    protected static $logName = 'security';
    
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
        }
        $context['login'] = $login;
        $this->addRecord($level,$message, $context);
    }
}
