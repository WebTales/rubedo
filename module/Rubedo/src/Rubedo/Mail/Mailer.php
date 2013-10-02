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
namespace Rubedo\Mail;

use Rubedo\Interfaces\Mail\IMailer;
use Rubedo\Services\Manager;
use Swift_Mailer;
use Swift_SmtpTransport;
use Swift_Message;
/**
 * Mailer Service
 *
 * Use SwiftMailer
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Mailer implements IMailer
{

    /**
     * Configuration for swift mail
     * @var array
     */
    protected static $options;
    
    
    
    /**
     * @return the $options
     */
    public function getOptions()
    {
        if(!isset(self::$options)){
           self::lazyloadConfig(); 
        }
        return Mailer::$options;
    }


    
	/**
     * @param multitype: $options
     */
    public static function setOptions($options)
    {
        Mailer::$options = $options;
    }

	/**
     * (non-PHPdoc) @see \Rubedo\Interfaces\Mail\IMailer::getNewMessage()
     */
    public function getNewMessage ()
    {
        return Swift_Message::newInstance();
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Interfaces\Mail\IMailer::sendMessage()
     */
    public function sendMessage ($message, &$failedRecipients = null)
    {
        if (! isset($this->_transport)) {
            $options = $this->getOptions();
            if (! isset($options['smtp'])) {
                throw new \Rubedo\Exceptions\Server('No smtp set in configuration', "Exception66");
            }
            $this->_transport = Swift_SmtpTransport::newInstance($options['smtp']['server'], $options['smtp']['port'], $options['smtp']['ssl'] ? 'ssl' : null);
            if (isset($options['smtp']['username'])) {
                $this->_transport->setUsername($options['smtp']['username'])->setPassword($options['smtp']['password']);
            }
        }
        if (! isset($this->_mailer)) {
            $this->_mailer = Swift_Mailer::newInstance($this->_transport);
        }
        
        // Send the message
        return $this->_mailer->send($message, $failedRecipients);
    }

    /**
     * Read configuration from global application config and load it for the current class
     */
    public static function lazyloadConfig ()
    {
        $config = Manager::getService('Application')->getConfig();
        if (isset($config['swiftmail'])) {
            self::setOptions($config['swiftmail']);
        }else{
            self::setOptions(array());
        }
    }
    
    /**
     * Is the service mailer active
     * 
     * True if a configuration is available.
     * 
     * @return boolean
     */
    public static function isActive(){
        if(!isset(self::$options)){
            self::lazyloadConfig();
        }
        if(count(self::$options)>0){
            return true;
        }else{
            return false;
        }
    }
}
