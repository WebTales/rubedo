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
namespace Rubedo\Backoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Zend\View\Model\JsonModel;
/**
 * Get Page URL Controller
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class XhrGetPageUrlController extends AbstractActionController
{

    public function indexAction ()
    {
        $pageId = $this->params()->fromPost('page-id');
        $locale = $this->params()->fromPost('locale',$this->params()->fromPost('workingLanguage'));
        if (!isset($locale)){
            $locale=Manager::getService("CurrentLocalization")->getCurrentLocalization();
        }
        if (!isset($locale)){
            $locale="en";
        }
        if (! $pageId) {
            throw new \Rubedo\Exceptions\User('This action needs a page-id as argument.', "Exception12");
        }
        $page = Manager::getService('Pages')->findById($pageId);
        if (! $page) {
            throw new \Rubedo\Exceptions\NotFound("The page-id doesn't match a page.", "Exception13");
        }
        $pageUrl = Manager::getService('Url')->getPageUrl($pageId,$locale);
        
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        $httpProtocol = $isHttps ? 'HTTPS' : 'HTTP';
        
        $targetSite = Manager::getService('Sites')->findById($page['site']);
        if (! is_array($targetSite['protocol']) || count($targetSite['protocol']) == 0) {
            throw new \Rubedo\Exceptions\Server('Protocol is not set for current site.', "Exception14");
        }
        $protocol = in_array($httpProtocol, $targetSite['protocol']) ? $httpProtocol : array_pop($targetSite['protocol']);
        $protocol = strtolower($protocol);
        
        $url = $protocol . '://' . Manager::getService('Sites')->getHost($page['site']) . '/' . ltrim($pageUrl, '/');
        
        $returnArray = array(
            'url' => $url
        );
        
        return new JsonModel($returnArray);
    }
}
