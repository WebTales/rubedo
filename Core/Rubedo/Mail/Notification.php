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
    protected static $sendNotification = true;
    
    
    
    /**
     * @return the $sendNotification
     */
    public static function getSendNotification ()
    {
        return Notification::$sendNotification;
    }

	/**
     * @param boolean $sendNotification
     */
    public static function setSendNotification ($sendNotification)
    {
        Notification::$sendNotification = $sendNotification;
    }

	/**
     * (non-PHPdoc) @see \Rubedo\Interfaces\Mail\IMailer::getNewMessage()
     */
    public function getNewMessage ()
    {
        return \Swift_Message::newInstance();
    }

    public function notify ($obj, $notificationType)
    {
        if (! self::$sendNotification) {
            return;
        }
        switch ($notificationType) {
            case 'published':
                return $this->_notifyPublished($obj);
                break;
        }
    }
    
    protected function _notifyPublished($obj){
        $twigVar = array();
        $twigVar['signature']='';
        $publishAuthor = $currentUser = Manager::getService('CurrentUser')->getCurrentUserSummary();
        $twigVar['publishAuthor']=(isset($publishAuthor['name']) && !empty($publishAuthor['name']))?$publishAuthor['name']:$publishAuthor['login'];
        $twigVar['title']=$obj['text'];
        $twigVar['directUrl']=(isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].'/backoffice/?content='.$obj['id'];
         
         
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("notification/published-body.html.twig");
        $mailBody = Manager::getService('FrontOfficeTemplates')->render($template, $twigVar);
         
         
        $mailService = Manager::getService('Mailer');
         
        $message = $mailService->getNewMessage();
        $message->setFrom(array('jbourdin@gmail.com'=>'Julien Bourdin'));
        $message->setTo(array('jbourdin@gmail.com'=>'Julien Bourdin'));
        $message->setSubject('[] Publication du contenu '. $obj['text']);
         
        $message->setBody($mailBody, 'text/html');
         
        $result = $mailService->sendMessage($message);
         
    }
    
    protected function _notifyRefused($obj){
    
    }
    
    protected function _notifyPending($obj){
    
    }
    
    
}
