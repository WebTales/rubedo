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
            return $this->redirect()->toUrl(strtolower(array_pop($this->_site['protocol'])) . '://' . $domain . '/' . $lang . $uri->getPath() . '?' . $uri->getQuery());
        }

        if ($domain && !$this->_site['useBrowserLanguage']) {
            setcookie('locale', $lang, strtotime('+1 year'), '/', $domain);
        }

        $config = Manager::getService("config");
        $defaultResources = array(
            "externalStyles" => array(),
            "externalScripts" => array(),
            "internalStyles" => array(),
            "internalScripts" => array()
        );
        $siteResources = !empty($this->_site['resources']) ? $this->_site['resources'] : $defaultResources;
        $this->viewData = array(
            'siteResources' => $siteResources,
            'activateMagic' => (isset($config['rubedo_config']['activateMagic']) && ($config['rubedo_config']['activateMagic'] == "1")) ? true : false,
            'angularLocale' => $lang
        );
        $viewModel = new ViewModel($this->viewData);
        $viewModel->setTerminal(true);

        return $viewModel;
    }
}