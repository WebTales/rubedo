<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

Use Rubedo\Controller\Action;

/**
 * Front Office Defautl Controller
 *
 * Invoked when calling front office URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class IndexController extends Zend_Controller_Action
{

    /**
     * Current front office page parameters
     * @var array
     */
    protected $_pageParams = array();

    /**
     * URL service
     * @var \Rubedo\Interfaces\Router\IUrl
     */
    protected $_serviceUrl;

    /**
     * page info service
     * @var \Rubedo\Interfaces\Content\IPage
     */
    protected $_servicePage;

    /**
     * FO Templates service
     * @var \Rubedo\Interfaces\Templates\IFrontOfficeTemplates
     */
    protected $_serviceTemplate;

    /**
     * Block service
     * @var \Rubedo\Interfaces\Content\IBlock
     */
    protected $_serviceBlock;

    /**
     * Main Action : render the Front Office view
	 * 
	 * @todo remove test
     */
    public function indexAction() {
		
        $this->_serviceUrl = Rubedo\Services\Manager::getService('Url');
        $this->_servicePage = Rubedo\Services\Manager::getService('PageContent');
        $this->_serviceTemplate = Rubedo\Services\Manager::getService('FrontOfficeTemplates');
        $this->_serviceBlock = Rubedo\Services\Manager::getService('BlockContent');

        $session = Rubedo\Services\Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        $calledUri = $this->getRequest()->getRequestUri();
        $pageId = $this->_serviceUrl->getPageId($calledUri);
        $this->_pageParams = $this->_servicePage->getPageInfo($pageId);

        $twigVar = $this->_pageParams;
        $twigVar['theme'] = $session->get('themeCSS', 'default');
        $twigVar['lang'] = $lang;

        $twigVar['title'] = 'Rubedo - Titre de page';
        
        $twigVar['css'][] = '/css/' . $twigVar['theme'] . ".bootstrap.min.css";
        $twigVar['css'][] = '/css/bootstrap-responsive.css';
        $twigVar['css'][] = '/css/rubedo.css';

        $twigVar['js'] = array("/js/jquery.js", "/js/bootstrap-transition.js", "/js/bootstrap-alert.js", "/js/bootstrap-modal.js", "/js/bootstrap-dropdown.js", "/js/bootstrap-scrollspy.js", "/js/bootstrap-tab.js", "/js/bootstrap-tooltip.js", "/js/bootstrap-popover.js", "/js/bootstrap-button.js", "/js/bootstrap-collapse.js", "/js/bootstrap-carousel.js", "/js/bootstrap-typeahead.js", );

        $twigVar['isLoggedIn'] = Rubedo\Services\Manager::getService('CurrentUser')->isAuthenticated();
        //$twigVar['canEdit'] = false;

        $content = $this->_serviceTemplate->render($this->_pageParams['template'], $twigVar);

        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getHelper('Layout')->disableLayout();

        $this->getResponse()->appendBody($content, 'default');

    }
}
