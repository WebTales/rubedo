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
use WebTales\MongoFilters\Filter;
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
     * @var array $placeholders placeholders for translating
     */
    protected $placeholders = array();

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
        return ($this->getOptions('isBackofficeSsl') ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . '/backoffice/?content=' . $id;
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
        $subject = 'Notification.publishedBody.subject';
        $this->placeholders['%title%'] = $obj['text'];
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
        $subject = 'Notification.refusedBody.subject';
        $this->placeholders['%title%'] = $obj['text'];
        return $this->sendNotification($userIdArray, $obj, $template, $subject);
    }

    protected function notifyPending ($obj)
    {
        $userIdArray = Manager::getService('Users')->findValidatingUsersByWorkspace($obj['writeWorkspace']);
        if (count($userIdArray) === 0) {
            return;
        }
        $template = 'pending-body.html.twig';
        $subject = 'Notification.pendingBody.subject';
        $this->placeholders['%title%'] = $obj['text'];
        return $this->sendNotification($userIdArray, $obj, $template, $subject, true);
    }

    /**
     * Send notifications, lang by lang.
     *
     * @param array $userIdArray Array of users ID
     * @param array $obj
     * @param string $template
     * @param string $subject
     * @param bool $hideTo
     *
     * @return bool success or failure
     */
    protected function sendNotification ($userIdArray, $obj, $template, $subject, $hideTo = false)
    {
        $userService = Manager::getService("Users");
        $filter = Filter::factory('InUid')->setValue($userIdArray);
        $userArray = $userService->getList($filter);
        $userArray = $this->sortByLang($userArray);

        $result = true;
        foreach ($userArray as $lang => $users) {
            $publishAuthor = Manager::getService('CurrentUser')->getCurrentUserSummary();
            $mailBody = $this->prepareBodyNotification($template, $publishAuthor, $obj, $lang);
            $toArray = $this->prepareToNotification($users);
            $subjectTranslated = $this->prepareSubjectNotification($subject, $lang);

            $message = $this->getNewMessage();
            $message->setSubject($subjectTranslated);
            $message->setBody($mailBody, 'text/html');
            if ($hideTo) {
                $message->setBcc($toArray);
            } else {
                $message->setTo($toArray);
            }
            $result = $result && $this->mailService->sendMessage($message);
        }
        return $result;
    }

    /**
     * Format array from database to mailer
     *
     * @param array $userArray array from database, one language
     * @return array array for mailer
     */
    protected function prepareToNotification($userArray)
    {
        $toArray = array();
        foreach ($userArray as $user) {
            $name = (!empty($user['name'])) ? $user['name'] : $user['login'];
            $toArray[$user['email']] = $name;
        }
        return $toArray;
    }

    protected function prepareSubjectNotification($subject, $lang)
    {
        $subject = Manager::getService('Translate')
            ->getTranslation(
                $subject,
                $lang,
                'en',
                $this->placeholders
            );
        $subject = '[' . $this->getOptions('defaultBackofficeHost') . '] ' . $subject;
        return $subject;
    }

    /**
     * Render twig of body notification
     *
     * @param string $template
     * @param array $currentUser
     * @param array $obj
     * @param string $lang
     *
     * @return string HTML frow twig render
     */
    protected function prepareBodyNotification($template, $currentUser, $obj, $lang)
    {
        $twigVar = array();
        $twigVar['publishingAuthor'] = (!empty($currentUser['name'])) ? $currentUser['name'] : $currentUser['login'];
        $twigVar['title'] = $obj['text'];
        $twigVar['directUrl'] = $this->directUrl($obj['id']);
        $twigVar['lang'] = $lang;
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("notification/" . $template);
        return Manager::getService('FrontOfficeTemplates')->render($template, $twigVar);
    }

    /**
     * Sort user array by user language
     *
     * @param array $userArray Array indexed by data/count
     * @return array Array indexed by language
     */
    protected function sortByLang($userArray)
    {
        $userByLangArray = array();
        if (!array_key_exists('data', $userArray)) {
            throw new \Rubedo\Exceptions\User('Translating sort is looking for a data key for users.');
        }
        foreach($userArray['data'] as $user) {
            $userByLangArray[$user['language']][] = $user;
        }
        return $userByLangArray;
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
