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
use Rubedo\Services\Manager;

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
class CssController extends Zend_Controller_Action
{
    function indexAction ()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $customThemeId = $this->getRequest()->getParam('id');
        $customTheme=Manager::getService('CustomThemes')->findById($customThemeId);
        $less = new lessc;
        $less->setImportDir(array(APPLICATION_PATH."/../public/components/webtales/bootstrap-less/less/"));
        $baseThemeOverrides=Zend_Json::decode($customTheme['lessVarsJson']);
        $less->setVariables($baseThemeOverrides);
        $compiledCss=$less->compileFile(APPLICATION_PATH."/../public/components/webtales/bootstrap-less/less/bootstrapoverrider.less");
        $this->getResponse()->clearBody();
        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearRawHeaders();
        $this->getResponse()->setHeader('Content-Type', 'text/css');
        $this->getResponse()->setHeader('Pragma', 'Public',true);
        $this->getResponse()->setBody($compiledCss);
        $this->getResponse()->sendHeaders();
    }
}
