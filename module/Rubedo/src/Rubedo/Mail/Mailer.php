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
     * (non-PHPdoc) @see \Rubedo\Interfaces\Mail\IMailer::getNewMessage()
     */
    public function getNewMessage ()
    {
        return \Swift_Message::newInstance();
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Interfaces\Mail\IMailer::sendMessage()
     */
    public function sendMessage ($message, &$failedRecipients = null)
    {
        if (! isset($this->_transport)) {
            $options = \Zend_Registry::get('swiftMail');
            if (! isset($options['smtp'])) {
                throw new \Rubedo\Exceptions\Server('No smtp set in configuration', "Exception66");
            }
            $this->_transport = \Swift_SmtpTransport::newInstance($options['smtp']['server'], $options['smtp']['port'], $options['smtp']['ssl'] ? 'ssl' : null);
            if (isset($options['smtp']['username'])) {
                $this->_transport->setUsername($options['smtp']['username'])->setPassword($options['smtp']['password']);
            }
        }
        if (! isset($this->_mailer)) {
            $this->_mailer = \Swift_Mailer::newInstance($this->_transport);
        }
        
        // Send the message
        return $this->_mailer->send($message, $failedRecipients);
    }
}
