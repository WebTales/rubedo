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
namespace Rubedo\Blocks\Controller;

use Rubedo\Services\Manager;

/**
 * Controller providing CRUD API for the MailingList JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 *
 */
class UnsubscribeController extends AbstractController
{

    protected $defaultTemplate = 'unsubscribe';

    /**
     * @var \Rubedo\Interfaces\Templates\IFrontOfficeTemplates
     */
    protected $frontOfficeTemplatesService;

    /**
     * @var \Rubedo\Interfaces\Collection\IMailingList
     */
    protected $mailingListService;
    public function __construct()
    {
        $this->frontOfficeTemplatesService = Manager::getService('FrontOfficeTemplates');
        $this->mailingListService = Manager::getService('MailingList');
    }
    public function indexAction()
    {
        $output = $this->params()->fromQuery();
        $email = $this->params()->fromPost("email");
        if ($this->getRequest()->isPost() && isset($email)) {
            $output['email'] = $email;
            if ((empty($email))) {
                $output['error'] = "Blocks.SignUp.emailConfirmError.invalidEmail";
            }
            $result = $this->mailingListService->unsubscribeFromAll($email);
            if ($result['success']) {
                $template = $this->frontOfficeTemplatesService->getFileThemePath("blocks/signup/unsubscribeok.html.twig");
            } else {
                $output['error'] = $result['msg'];
            }
        }
        if (!isset($template)) {
            $template = $this->frontOfficeTemplatesService->getFileThemePath("blocks/" . $this->defaultTemplate . ".html.twig");
        }
        return $this->_sendResponse($output, $template);
    }
}