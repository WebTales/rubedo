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
use Rubedo\Exceptions\NotFound;

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

    function indexAction()
    {
        $customThemeId = $this->params('id');
        $customThemeVersion = $this->params('version');
        $customTheme = Manager::getService('CustomThemes')->findById($customThemeId);
        if (! $customTheme || $customTheme['version'] != $customThemeVersion) {
            throw new NotFound('Custom Theme not Found');
        }
        $less = new \lessc();
        $less->setImportDir(array(
            APPLICATION_PATH . "/public/components/webtales/bootstrap-less/less/"
        ));
        $baseThemeOverrides = Json::decode($customTheme['lessVarsJson'], Json::TYPE_ARRAY);
        $less->setVariables($baseThemeOverrides);
        $compiledCss = $less->compileFile(APPLICATION_PATH . "/public/components/webtales/bootstrap-less/less/bootstrapoverrider.less");
        
        $config = manager::getService('Application')->getConfig();
        if (isset($config['rubedo_config']['minify']) && $config['rubedo_config']['minify'] == true) {
            $compiledCss = \Minify_CSS::minify($compiledCss, array(
                'preserveComments' => false
            ));
        }
        
        $publicThemePath = APPLICATION_PATH . '/public/theme';
        $composedPath = $publicThemePath . '/custom';
        if (! file_exists($composedPath)) {
            mkdir($composedPath, 0777);
        }
        
        $composedPath = $composedPath . '/' . $customThemeId . '/' . $customThemeVersion;
        if (! file_exists($composedPath)) {
            mkdir($composedPath, 0777, true);
        }
        $targetPath = $composedPath . '/theme.css';
        if (file_put_contents($targetPath, $compiledCss)) {
            $stream = fopen($targetPath, 'r');
            $response = new \Zend\Http\Response\Stream();
            $response->setStream($stream);
        } else {
            $response = new \Zend\Http\Response();
            $response->setContent($compiledCss);
        }
        
        $response->getHeaders()->addHeaders(array(
            'Content-type' => 'text/css',
            'Pragma' => 'Public',
            'Cache-Control' => 'public, max-age=' . 7 * 24 * 3600,
            'Expires' => date(DATE_RFC822, strtotime("7 day"))
        ));
        
        return $response;
    }
}
