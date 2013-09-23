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
namespace Rubedo\Frontoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Zend\Json\Json;

/**
 * Controller providing css for custom themes
 *
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 *         
 */
class CssController extends AbstractActionController
{
    function indexAction ()
    {
        $customThemeId = $this->params()->fromQuery('id');
        $customTheme=Manager::getService('CustomThemes')->findById($customThemeId);
        $less = new \lessc;
        $less->setImportDir(array(APPLICATION_PATH."/public/components/webtales/bootstrap-less/less/"));
        $baseThemeOverrides=Json::decode($customTheme['lessVarsJson'], Json::TYPE_ARRAY);
        $less->setVariables($baseThemeOverrides);
        $compiledCss=$less->compileFile(APPLICATION_PATH."/public/components/webtales/bootstrap-less/less/bootstrapoverrider.less");
        $response = new \Zend\Http\Response();
        $response->getHeaders()->addHeaders(array(
            'Content-type' => 'text/css',
            'Pragma' => 'Public'
        ));
        $response->setContent($compiledCss);
        return($response);
    }
}
