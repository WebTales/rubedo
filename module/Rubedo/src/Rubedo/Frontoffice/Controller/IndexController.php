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

use Rubedo\Collection\AbstractCollection;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Front Office Defautl Controller
 *
 * Invoked when calling front office URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class IndexController extends AbstractActionController
{

    /**
     * Current front office page parameters
     *
     * @var array
     */
    protected $_pageParams = array();

    /**
     * URL service
     *
     * @var \Rubedo\Interfaces\Router\IUrl
     */
    protected $_serviceUrl;

    /**
     * page info service
     *
     * @var \Rubedo\Interfaces\Content\IPage
     */
    protected $_servicePage;

    /**
     * FO Templates service
     *
     * @var \Rubedo\Interfaces\Templates\IFrontOfficeTemplates
     */
    protected $_serviceTemplate;

    /**
     * Block service
     *
     * @var \Rubedo\Interfaces\Content\IBlock
     */
    protected $_serviceBlock;

    /**
     * ID of the current page
     *
     * @var string
     */
    protected $_pageId;

    /**
     * current page data
     *
     * @var array
     */
    protected $_pageInfos;

    /**
     * current mask object
     *
     * @var array
     */
    protected $_mask;

    /**
     * array of parent IDs
     *
     * @var array
     */
    protected $_rootlineArray;

    /**
     * ID of the column to display main content instead of page content if
     * content-id given
     *
     * @var string
     */
    protected $_mainCol = null;

    /**
     * Main Action : render the Front Office view
     * @todo Add all API keys (google map, disqus ...) and missing informations from v2.2
     */
    public function indexAction()
    {
        if ($this->params()->fromQuery('tk', null)) {
            $redirectParams = array(
                'action' => 'index',
                'controller' => 'tiny'
            );
            $options = array('query' => $this->params()->fromQuery());
            return $this->redirect()->toRoute('frontoffice/default', $redirectParams, $options);
        }

        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        $httpProtocol = $isHttps ? 'HTTPS' : 'HTTP';

        // init service variables
        $this->_serviceUrl = Manager::getService('Url');
        $this->_servicePage = Manager::getService('PageContent');

        $this->_pageId = $this->params()->fromRoute('pageId');
        $this->_servicePage->setCurrentPage($this->_pageId);

        if (!$this->_pageId) {
            throw new \Rubedo\Exceptions\NotFound('No Page found', "Exception2");
        }


        $this->_pageInfo = Manager::getService('Pages')->findById($this->_pageId);

        if ($this->_pageInfo === NULL) {
            $wasFiltered1 = AbstractCollection::disableUserFilter();
            $attemptedPage = Manager::getService('Pages')->findById($this->_pageId);
            $site = Manager::getService('Sites')->findById($attemptedPage['site']);
            $homePageId = $site['homePage'];
            AbstractCollection::disableUserFilter($wasFiltered1);

            if ($this->_pageId == $homePageId) {
                throw new \Rubedo\Exceptions\Server('You do not have access to the current site');
            }
            $uri = $this->getRequest()->getUri();
            $domain = $uri->getHost();
            return $this->redirect()->toUrl(strtolower(array_pop($site['protocol'])) . '://' . $domain);
        }

        $wasFiltered1 = AbstractCollection::disableUserFilter();
        $this->_site = Manager::getService('Sites')->findById($this->_pageInfo['site']);
        AbstractCollection::disableUserFilter($wasFiltered1);

        // ensure protocol is authorized for this site
        if (!is_array($this->_site['protocol']) || count($this->_site['protocol']) == 0) {
            throw new \Rubedo\Exceptions\Server('Protocol is not set for current site', "Exception14");
        }

        $uri = $this->getRequest()->getUri();
        $domain = $uri->getHost();

        /**
         *
         * @todo rewrite this in ZF2 way
         */
        if (!in_array($httpProtocol, $this->_site['protocol'])) {
            return $this->redirect()->toUrl(strtolower(array_pop($this->_site['protocol'])) . '://' . $domain . $uri->getPath() . '?' . $uri->getQuery());
        }

        AbstractCollection::setIsFrontEnd(true);

        // context
        $cookieValue = $this->getRequest()->getCookie('locale');
        if (!isset($cookieValue['locale'])) {
            $cookieValue = null;
        } else {
            $cookieValue = $cookieValue['locale'];
        }
        $lang = Manager::getService('CurrentLocalization')->resolveLocalization($this->_site['id'], $this->params('locale'), $cookieValue);
        if ($lang && !$this->params('locale')) {
            return $this->redirect()->toUrl(strtolower(array_pop($this->_site['protocol'])) . '://' . $domain . '/' . $lang . $uri->getPath() . '?' . $uri->getQuery())->setStatusCode(301);
        }

        if ($domain && !$this->_site['useBrowserLanguage']) {
            setcookie('locale', $lang, strtotime('+1 year'), '/', $domain);
        }

        $config = Manager::getService("config");
        $minifyResources=false;
        if (isset($config['rubedo_config']['minify']) && $config['rubedo_config']['minify'] == "1") {
            $minifyResources=true;
        }
        $defaultResources = array(
            "externalStyles" => array(),
            "externalScripts" => array(),
            "internalStyles" => array(),
            "internalScripts" => array()
        );
        $siteResources = !empty($this->_site['resources']) ? $this->_site['resources'] : $defaultResources;
        $theme = $this->_site['theme'];
        /** @var \Rubedo\Collection\Themes $themesService */
        $themesService = Manager::getService('Themes');
        $themeObj = $themesService->findByName($theme);
        if ($themeObj) {
            /** @var \Rubedo\Collection\DAM $DAMService */
            $DAMService = Manager::getService('DAM');
            $filters = Filter::factory()
                ->addFilter(
                    Filter::factory('Value')
                        ->setName('loadOnLaunch')
                        ->setValue(true)
                )
                ->addFilter(
                    Filter::factory('Value')
                        ->setName('themeId')
                        ->setValue($themeObj['id'])
                )
                ->addFilter(
                    Filter::factory('OperatorToValue')
                        ->setName('title')
                        ->setOperator('$regex')
                        ->setValue('.+(css|js)$')
                );
            $themeFilesToLoad = $DAMService->getList($filters)['data'];
            foreach ($themeFilesToLoad as &$fileToLoad) {
                $themeFile = '/'
                    . implode('/', $this->discoverDirNames(array(), $fileToLoad['directory']))
                    . '/' . $fileToLoad['title'];
                $extension = substr(strrchr($themeFile, '.'), 1);
                if ($extension == 'css'&&!$minifyResources) {
                    $siteResources['internalStyles'][] = $themeFile;
                } elseif ($extension == 'js') {
                    $siteResources['internalScripts'][] = $themeFile;
                }
            }
        }

        $themeName = strtolower($theme);
        $propagatedSiteTheme = $themeName;
        $includeBaseBootstrap=true;
        if (isset($config['templates']['themes'][$themeName])) {
            $theme = $config['templates']['themes'][$themeName];
            if (isset($theme["noBootstrap"])&&$theme["noBootstrap"]){
                $includeBaseBootstrap=false;
            }
            $prepend = '/theme/' . $themeName;
            $propagatedSiteTheme = $themeName;
            if (isset($theme['css'])&&!$minifyResources) {
                foreach ($theme['css'] as $css) {
                    $siteResources['internalStyles'][] = strpos($css, '//') === false ? $prepend . $css : $css;
                }
            }
            if (isset($theme['js'])) {
                foreach ($theme['js'] as $js) {
                    $siteResources['internalScripts'][] = strpos($js, '//') === false ? $prepend . $js : $js;
                }
            }
            if (isset($theme['angularModules'])) {
                if (!isset($siteResources['angularModulesPaths'])) {
                    $siteResources['angularModulesPaths'] = array();
                }
                if (!isset($siteResources['angularModules'])) {
                    $siteResources['angularModules'] = array();
                }
                foreach ($theme['angularModules'] as $angularModule => $angularModulePath) {
                    $siteResources['angularModulesPaths'][] = strpos($angularModulePath, '//') === false ? $prepend . $angularModulePath : $angularModulePath;
                    $siteResources['angularModules'][] = $angularModule;
                }
            }
        }
        if (isset($config['extension_paths'])) {
            foreach ($config['extension_paths'] as $extensionName => $extension) {
                if (!isset($extension['path'])) {
                    continue;
                }
                $extensionPath = '/extension-path/' . $extensionName . '/';
                if (isset ($extension['css'])) {
                    foreach ($extension['css'] as $extensionCss) {
                        $siteResources['internalStyles'][] = strpos($extensionCss, '//') === false ? $extensionPath . $extensionCss : $extensionCss;
                    }
                }
                if (isset ($extension['js'])) {
                    foreach ($extension['js'] as $extensionJs) {
                        $siteResources['internalScripts'][] = strpos($extensionJs, '//') === false ? $extensionPath . $extensionJs : $extensionJs;
                    }
                }
                if (isset ($extension['angularModules'])) {
                    if (!isset($siteResources['angularModulesPaths'])) {
                        $siteResources['angularModulesPaths'] = array();
                    }
                    if (!isset($siteResources['angularModules'])) {
                        $siteResources['angularModules'] = array();
                    }
                    foreach ($extension['angularModules'] as $angularModule => $angularModulePath) {
                        $siteResources['angularModulesPaths'][] = strpos($angularModulePath, '//') === false ? $extensionPath . $angularModulePath : $angularModulePath;
                        $siteResources['angularModules'][] = $angularModule;
                    }
                }
            }
        }

        $googleAnalyticsKey = isset($this->_site['googleAnalyticsKey']) ? $this->_site['googleAnalyticsKey'] : false;

        $this->viewData = array(
            'siteResources' => $siteResources,
            'googleAnalyticsKey' => $googleAnalyticsKey,
            'activateMagic' => (isset($config['rubedo_config']['activateMagic']) && ($config['rubedo_config']['activateMagic'] == "1")) ? true : false,
            'angularLocale' => $lang,
            'siteTheme' => $propagatedSiteTheme,
            'includeBaseBootstrap'=>$includeBaseBootstrap,
            'minifyResources'=>$minifyResources
        );

        $viewModel = new ViewModel($this->viewData);
        $viewModel->setTerminal(true);

        return $viewModel;
    }

    function discoverDirNames($dirs, $nextDir)
    {
        if ($nextDir === 'root') {
            return $dirs;
        }
        /** @var \Rubedo\Collection\Directories $dirService */
        $dirService = Manager::getService('Directories');
        $directory = $dirService->findById($nextDir);
        if ($directory) {
            array_unshift($dirs, $directory['text']);
        }
        return $this->discoverDirNames($dirs, $directory['parentId']);
    }
}