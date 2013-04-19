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
    protected static $_sendNotification = true;
    
    protected static $_options = array();
    
    
    /**
     * @return the $sendNotification
     */
    public static function getSendNotification ()
    {
        return Notification::$_sendNotification;
    }

	/**
     * @param boolean $sendNotification
     */
    public static function setSendNotification ($sendNotification)
    {
        Notification::$_sendNotification = $sendNotification;
    }

    
    
    public function getOptions ($name,$defaultValue=null)
    {
        if(isset(self::$_options[$name])){
            return self::$_options[$name];
        }else{
            return $defaultValue;
        }
        
    }

    public static function setOptions ($name,$value)
    {
        Notification::$_options[$name] = $value;
    }


    public function getNewMessage ()
    {
        $this->mailService = Manager::getService('Mailer');
         
        $message = $this->mailService->getNewMessage();
        $message->setFrom(array($this->getOptions('fromEmailNotification')));
        
        return $message;
    }

    public function notify ($obj, $notificationType)
    {
        if (! self::$_sendNotification) {
            return;
        }
        switch ($notificationType) {
            case 'published':
                return $this->_notifyPublished($obj);
                break;
            case 'refused':
                return $this->_notifyRefused($obj);
                break;
        }
    }
    
    protected function setTo($message,$userIdArray){
        $userService = Manager::getService("Users");
        $toArray = array();
        foreach($userIdArray as $userId){
            $user = $userService->findById($userId);
            $name = (isset($user['name']) && ! empty($user['name'])) ? $user['name'] : $user['login'];
            $toArray[$user['email']]=$name;
            
            
        }
        $message->setTo($toArray);
    }

    protected function _directUrl($id){
        return ($this->getOptions('isBackofficeSsl') ? 'https' : 'http') . '://' . $this->getOptions('defaultBackofficeHost') . '/backoffice/?content=' . $id;
    }
    
    protected function _notifyPublished ($obj)
    {
        if (! isset($obj["lastPendingUser"])) {
            return;
        }
        $twigVar = array();
        $publishAuthor = Manager::getService('CurrentUser')->getCurrentUserSummary();
        $twigVar['publishingAuthor'] = (isset($publishAuthor['name']) && ! empty($publishAuthor['name'])) ? $publishAuthor['name'] : $publishAuthor['login'];
        $twigVar['title'] = $obj['text'];
        $twigVar['directUrl'] = $this->_directUrl($obj['id']);
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("notification/published-body.html.twig");
        $mailBody = Manager::getService('FrontOfficeTemplates')->render($template, $twigVar);
        
        $message = $this->getNewMessage();
        $this->setTo($message, array(
            $obj["lastPendingUser"]["id"]
        ));
        $message->setSubject('[' . $this->getOptions('defaultBackofficeHost') . '] Publication du contenu "' . $obj['text'] . '"');
        $message->setBody($mailBody, 'text/html');
        
        $result = $this->mailService->sendMessage($message);
        return $result;
    }
    
    protected function _notifyRefused($obj){
        if (! isset($obj["lastPendingUser"])) {
            return;
        }
        $twigVar = array();
        $publishAuthor = Manager::getService('CurrentUser')->getCurrentUserSummary();
        $twigVar['publishingAuthor'] = (isset($publishAuthor['name']) && ! empty($publishAuthor['name'])) ? $publishAuthor['name'] : $publishAuthor['login'];
        $twigVar['title'] = $obj['text'];
        $twigVar['directUrl'] = $this->_directUrl($obj['id']);
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("notification/refused-body.html.twig");
        $mailBody = Manager::getService('FrontOfficeTemplates')->render($template, $twigVar);
        
        $message = $this->getNewMessage();
        $this->setTo($message, array(
            $obj["lastPendingUser"]["id"]
        ));
        $message->setSubject('[' . $this->getOptions('defaultBackofficeHost') . '] Refus du contenu "' . $obj['text'] . '"');
        $message->setBody($mailBody, 'text/html');
        
        $result = $this->mailService->sendMessage($message);
        return $result;
    }
    
    protected function _notifyPending($obj){
    
    }
    
    
}
