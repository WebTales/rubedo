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
     */
    public function indexAction() {

        $this->_serviceUrl = Rubedo\Services\Manager::getService('Url');
        $this->_servicePage = Rubedo\Services\Manager::getService('Page');
        $this->_serviceTemplate = Rubedo\Services\Manager::getService('FrontOfficeTemplates');
        $this->_serviceBlock = Rubedo\Services\Manager::getService('Block');

        $session = Rubedo\Services\Manager::getService('Session');
        $lang = $session->get('lang', 'fr');
        $this->_serviceTemplate->init($lang);

        $calledUri = $this->getRequest()->getRequestUri();
        $pageId = $this->_serviceUrl->getPageId($calledUri);
        $this->_pageParams = $this->_servicePage->getPageInfo($pageId);

        $twigVar = $this->_pageParams;
        $twigVar['theme'] = $session->get('themeCSS', 'default');
        $twigVar['lang'] = $lang;

        $response = Action::getInstance()->action('index', 'nav-bar', 'blocks');
        $twigVar['navbar_content'] = $response->getBody('content');
        /*
         foreach ($this->_pageParams['blocks'] as $block) {
         $twigVar = array_merge($twigVar, $this->_serviceBlock->getBlockData($block, $pageId, $this));
         }
         *
         */
        /*
         if ($pageId == "newpage") {

         $headlineContentArray = $this->_serviceBlock->getBlockData(array('Module' => 'HeadLine', 'Input' => null, 'Output' => 'headline_content'), $pageId, $this);
         $headlineContent = $headlineContentArray['headline_content'];

         $newTwigVar['rows'] = array();
         $newTwigVar['rows'][] = array('columns' => array(1 => array('span' => 9, 'blocks' => array( array('template' => 'root/blocks/singlecontent.html', 'data' => $headlineContent))), 2 => array('span' => 3, 'blocks' => array( array('template' => 'root/blocks/carrousel.html', 'data' => array('data' => $twigVar['carousel_content']))))));
         $newTwigVar['rows'][] = array('columns' => array(1 => array('span' => 12, 'blocks' => array( array('template' => 'root/blocks/contentlist.html', 'data' => array('data' => $twigVar['contentlist_content'])))), ));

         $newTwigVar['theme'] = $twigVar['theme'];
         $newTwigVar["navbar_content"] = $twigVar["navbar_content"];

         $twigVar = $newTwigVar;

         }
         *
         */

        $twigVar['title'] = 'Rubedo - Titre de page';
        $twigVar['css'] = array('/css/rubedo.css', '/css/bootstrap-responsive.css');
        $twigVar['css'][] = '/css/' . $twigVar['theme'] . ".bootstrap.min.css";

        $twigVar['js'] = array("/js/jquery.js", "/js/bootstrap-transition.js", "/js/bootstrap-alert.js", "/js/bootstrap-modal.js", "/js/bootstrap-dropdown.js", "/js/bootstrap-scrollspy.js", "/js/bootstrap-tab.js", "/js/bootstrap-tooltip.js", "/js/bootstrap-popover.js", "/js/bootstrap-button.js", "/js/bootstrap-collapse.js", "/js/bootstrap-carousel.js", "/js/bootstrap-typeahead.js", );

		
        $content = $this->_serviceTemplate->render($this->_pageParams['template'], $twigVar);

        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getHelper('Layout')->disableLayout();

        $this->getResponse()->appendBody($content, 'default');

    }

}
