<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IEmails;
use Rubedo\Services\Manager;
use Swift_Image;
use Swift_Message;

/**
 * Service to handle Emails
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class Emails extends AbstractCollection implements IEmails
{

    public function __construct()
    {
        $this->_collectionName = 'Emails';
        $this->mailer = Manager::getService('Mailer');
        $this->swiftMessage = $this->mailer->getNewMessage();
        parent::__construct();
    }
    /**
     * @var Swift_Message
     */
    protected $swiftMessage;

    /**
     * @var \Rubedo\Mail\Mailer
     */
    protected $mailer;

    /**
     * Return the html from twig template
     *
     * @param string $title
     * @param array $bodyProperties
     * @param array $rows
     * @param bool $cid transform image in cid or link ?
     *
     * @return String html
     */
    public function htmlConstructor($title, array $bodyProperties, array $rows, $cid = true)
    {
        $this->parseImages($rows, $cid);

        $vars = array(
            'properties' => $bodyProperties,
            'rows' => $rows,
            'title' => $title,
        );
        $template = Manager::getService('FrontOfficeTemplates')->render("mail/newsletter.html.twig", $vars);
        return $template;
    }

    /**
     * Looking for imageComponents in $rows for replace id by img src
     *
     * @param array $rows
     * @param bool $cid
     */
    protected function parseImages(array &$rows, $cid = true)
    {
        foreach ($rows as &$row) {
            foreach ($row['cols'] as &$col) {
                foreach ($col['components'] as &$component) {
                    switch ($component['type']) {
                        case 'imageComponent':
                            $file_id = $this->findImageFromDam($component['config']['image']);
                            if ($cid) {
                                $component['config']['image'] = $this->attachImageFromFiles($file_id);
                            } else {
                                $component['config']['image'] = '/image?file-id=' . $file_id;
                            }
                            break;
                        case 'textComponent':
                            if ($cid) {
                                $component['config']['html'] = preg_replace_callback(
                                    "/src=[\"|'](.*file-id=(.*))[\"|']/U",
                                    function ($matches) {
                                        return 'src="' . $this->attachImageFromFiles($matches[2]) . '"';
                                    },
                                    $component['config']['html']);;
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * Find a file in DAM, and get the id of originalFileId
     *
     * @param String $id
     * @throws \Rubedo\Exceptions\NotFound
     *
     * @return String cid
     */
    protected function findImageFromDam($id)
    {
        $dam = Manager::getService('Dam')->findById($id);
        return $dam['originalFileId'];
    }

    /**
     * Find a file and attach it. Return cid
     *
     * @param String $id
     * @throws \Rubedo\Exceptions\NotFound
     *
     * @return String cid
     */
    protected function attachImageFromFiles($id)
    {
        $file = Manager::getService('Files')->findById($id);
        if (!$file instanceof \MongoGridFSFile) {
            throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
        }
        $meta = $file->file;
        return $this->swiftMessage->embed(Swift_Image::newInstance($file->getBytes(), $meta['filename'], $meta['Content-Type']));
    }

    /**
     * @return \Swift_Message
     */
    public function getSwiftMessage()
    {
        return $this->swiftMessage;
    }

    public function setMessageHTML($html)
    {
        $this->swiftMessage->setBody($html, 'text/html');
        return $this;
    }

    public function setSubject($text)
    {
        $this->swiftMessage->setSubject($text);
        return $this;
    }

    public function setMessageTXT($txt)
    {
        $this->swiftMessage->addPart($txt, 'text/plain');
        return $this;
    }

    public function setTo(array $to)
    {
        $this->swiftMessage->setTo($to);
    }

    public function setFrom(array $list)
    {
        $from = array();
        if (!empty($list['fromAddress']) && !empty($list['fromName'])) {
            $from[$list['fromAddress']] = $list['fromName'];
        } elseif (!empty($list['fromAddress'])) {
            $from[] = $list['fromAddress'];
        } else {
            $config = Manager::getService('config');
            $rubedoMail = $config['rubedo_config']['fromEmailNotification'];
            $from[$rubedoMail] = 'Rubedo';
        }
        $this->swiftMessage->setFrom($from);
    }


    public function send()
    {
        return $this->mailer->sendMessage($this->swiftMessage);
    }
}
