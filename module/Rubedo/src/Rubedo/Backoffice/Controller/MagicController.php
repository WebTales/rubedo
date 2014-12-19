<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Backoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Zend\View\Model\JsonModel;


/**
 * Controller providing Magic Query admin features
 *
 *
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 *
 */
class MagicController extends AbstractActionController
{
    public function refreshUserRecommendationsAction()
    {
        $result = Manager::getService("UserRecommendations")->build();
        return new JsonModel($result);
    }

    public function refreshItemRecommendationsAction()
    {
        $result = Manager::getService("ItemRecommendations")->build();
        return new JsonModel($result);
    }
}
