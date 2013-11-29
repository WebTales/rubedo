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
namespace Rubedo\Interfaces\Mail;

/**
 * Mailer Service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
Interface INotification
{

    /**
     * Return the message object
     *
     * @return object message
     */
    public function getNewMessage();

    /**
     * Get the flag sendNotification
     *
     * @return boolean
     */
    public static function getSendNotification();

    /**
     * Set the flag sendNotification
     *
     * @param boolean $sendNotification
     */
    public static function setSendNotification($sendNotification);

    /**
     * Get the option $name. If not exist, return $defaultValue
     *
     * @param string|int $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getOptions($name, $defaultValue = null);

    /**
     * Add an option entry
     *
     * @param string|int $name
     * @param mixed $value
     */
    public static function setOptions($name, $value);

    /**
     * Send the notification
     *
     * @param object $obj
     * @param string $notificationType
     * @return boolean result
     */
    public function notify($obj, $notificationType);
}
