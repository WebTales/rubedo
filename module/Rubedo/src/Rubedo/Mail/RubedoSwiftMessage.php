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
namespace Rubedo\Mail;

use Swift_Message;

/**
 * Class Rubedo_Swift_Message
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 *
 * Overwrite Swift_Message class to add control on email address and avoid exception
 */
class Rubedo_Swift_Message extends Swift_Message
{

    /**
     * Create a new Message.
     *
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     *
     * @return Swift_Message
     */
    public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
    {
        return new self($subject, $body, $contentType, $charset);
    }

    /**
     * Set the from address of this message.
     *
     * You may pass an array of addresses if this message is from multiple people.
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param string|array $addresses
     * @param string       $name      optional
     * @throw \Exception
     *
     * @return Swift_Mime_SimpleMessage
     */
    public function setFrom($addresses, $name = null)
    {
        try {
            return parent::setFrom($addresses, $name);
        } catch(\Exception $e) {
            $address = "";

            if(is_array($addresses)) {
                if(count($addresses) > 0) {
                    foreach($addresses as $email) {
                        $address .= $email.", ";
                    }

                    $address = substr($address, 0, -2);
                }
            } else {
                $address = $addresses;
            }

            throw new \Rubedo\Exceptions\Server('Bad email sender value : %1$s', "Exception104", $address);
        }
    }

}
