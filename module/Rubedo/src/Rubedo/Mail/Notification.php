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

use Rubedo\Interfaces\Mail\INotification, Rubedo\Services\Manager;

/**
 * Mailer Service
 *
 * Use SwiftMailer
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Notification implements INotification
{

    protected static $sendNotificationFlag;

    protected static $options = array();

    /**
     *
     * @return the $sendNotification
     */
    public static function getSendNotification ()
    {
        if (! isset(self::$sendNotificationFlag)) {
            self::lazyloadConfig();
        }
        return Notification::$sendNotificationFlag;
    }

    /**
     *
     * @param boolean $sendNotification            
     */
    public static function setSendNotification ($sendNotification)
    {
        Notification::$sendNotificationFlag = $sendNotification;
    }

    public function __construct ()
    {
        if (! isset(self::$sendNotificationFlag)) {
            self::lazyloadConfig();
        }
    }

    public function getOptions ($name, $defaultValue = null)
    {
        if (isset(self::$options[$name])) {
            return self::$options[$name];
        } else {
            return $defaultValue;
        }
    }

    public static function setOptions ($name, $value)
    {
        Notification::$options[$name] = $value;
    }

    public function getNewMessage ()
    {
        $this->mailService = Manager::getService('Mailer');
        
        $message = $this->mailService->getNewMessage();
        $message->setFrom(array(
            $this->getOptions('fromEmailNotification') => 'Rubedo'
        ));
        
        return $message;
    }

    public function notify ($obj, $notificationType)
    {
        if (! self::$sendNotificationFlag) {
            return;
        }
        switch ($notificationType) {
            case 'published':
                return $this->notifyPublished($obj);
                break;
            case 'refused':
                return $this->notifyRefused($obj);
                break;
            case 'pending':
                return $this->notifyPending($obj);
                break;
        }
    }

    protected function directUrl ($id)
    {
        return ($this->getOptions('isBackofficeSsl') ? 'https' : 'http') . '://' . $this->getOptions('defaultBackofficeHost') . '/backoffice/?content=' . $id;
    }

    protected function notifyPublished ($obj)
    {
        if (! isset($obj["lastPendingUser"])) {
            return;
        }
        $userIdArray = array(
            $obj["lastPendingUser"]["id"]
        );
        $template = 'published-body.html.twig';
        $subject = '[' . $this->getOptions('defaultBackofficeHost') . '] Publication du contenu "' . $obj['text'] . '"';
        return $this->sendNotification($userIdArray, $obj, $template, $subject);
    }

    protected function notifyRefused ($obj)
    {
        if (! isset($obj["lastPendingUser"])) {
            return;
        }
        $userIdArray = array(
            $obj["lastPendingUser"]["id"]
        );
        $template = 'refused-body.html.twig';
        $subject = '[' . $this->getOptions('defaultBackofficeHost') . '] Refus du contenu "' . $obj['text'] . '"';
        return $this->sendNotification($userIdArray, $obj, $template, $subject);
    }

    protected function notifyPending ($obj)
    {
        $userIdArray = Manager::getService('Users')->findValidatingUsersByWorkspace($obj['writeWorkspace']);
        if (count($userIdArray) === 0) {
            return;
        }
        $template = 'pending-body.html.twig';
        $subject = '[' . $this->getOptions('defaultBackofficeHost') . '] Soumission pour validation d\'un contenu "' . $obj['text'] . '"';
        return $this->sendNotification($userIdArray, $obj, $template, $subject, true);
    }

    protected function sendNotification ($userIdArray, $obj, $template, $subject, $hideTo = false)
    {
        $twigVar = array();
        $publishAuthor = Manager::getService('CurrentUser')->getCurrentUserSummary();
        $twigVar['publishingAuthor'] = (isset($publishAuthor['name']) && ! empty($publishAuthor['name'])) ? $publishAuthor['name'] : $publishAuthor['login'];
        $twigVar['title'] = $obj['text'];
        $twigVar['directUrl'] = $this->directUrl($obj['id']);
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("notification/" . $template);
        $mailBody = Manager::getService('FrontOfficeTemplates')->render($template, $twigVar);
        
        $message = $this->getNewMessage();
        $this->setTo($message, $userIdArray, $hideTo);
        $message->setSubject($subject);
        $message->setBody($mailBody, 'text/html');
        
        $result = $this->mailService->sendMessage($message);
        return $result;
    }

    protected function setTo ($message, $userIdArray, $hideTo = false)
    {
        $userService = Manager::getService("Users");
        $toArray = array();
        foreach ($userIdArray as $userId) {
            $user = $userService->findById($userId);
            $name = (isset($user['name']) && ! empty($user['name'])) ? $user['name'] : $user['login'];
            $toArray[$user['email']] = $name;
        }
        if ($hideTo) {
            $message->setBcc($toArray);
        } else {
            $message->setTo($toArray);
        }
    }

    /**
     * Read configuration from global application config and load it for the current class
     */
    public static function lazyloadConfig ()
    {
        $config = Manager::getService('config');
        $options = $config['rubedo_config'];
        if (isset($options['enableEmailNotification'])) {
            \Rubedo\Mail\Notification::setSendNotification(true);
            \Rubedo\Mail\Notification::setOptions('defaultBackofficeHost', isset($options['defaultBackofficeHost']) ? $options['defaultBackofficeHost'] : null);
            \Rubedo\Mail\Notification::setOptions('isBackofficeSSL', isset($options['isBackofficeSSL']) ? $options['isBackofficeSSL'] : false);
            \Rubedo\Mail\Notification::setOptions('fromEmailNotification', isset($options['fromEmailNotification']) ? $options['fromEmailNotification'] : null);
        }
    }
}
