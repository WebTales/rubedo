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
namespace Rubedo\Frontoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Rubedo\Version\Version;
use Zend\View\Model\ViewModel;


/**
 * Controller providing sitemap generation
 *
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 *
 */
class ServiceworkerController extends AbstractActionController
{

    function indexAction()
    {
        header('Content-Type: text/javascript');
        header('Cache-Control: no-cache');
        $siteName = $_SERVER["HTTP_HOST"];
        $currentSite=Manager::getService("Sites")->findByHost($siteName);
        if (!$currentSite){
            throw new \Rubedo\Exceptions\NotFound('Site not found');
        }
        $rubedoVersion=Version::getVersion();
        $compsVersion=Version::getComponentsVersion();
        $swCacheKey=$currentSite["theme"]."S".$currentSite["version"]."R".$rubedoVersion;
        if(!empty($compsVersion["extensions"])&&is_array($compsVersion["extensions"])){
            $swCacheKey=$swCacheKey."E";
            foreach($compsVersion["extensions"] as $key=>$value){
                $swCacheKey=$swCacheKey.$key.":".$value;
            }
        }
        $viewModel = new ViewModel([
            "cacheKey"=>$swCacheKey
        ]);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

}