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
Use Rubedo\Services\Manager;

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
        //init service variables
        $this->_serviceUrl = Manager::getService('Url');
        $this->_servicePage = Manager::getService('PageContent');
        $this->_serviceTemplate = Manager::getService('FrontOfficeTemplates');
        $this->_session = Manager::getService('Session');
		
        //context
        $lang = $this->_session->get('lang', 'fr');
        $isLoggedIn = Manager::getService('CurrentUser')->isAuthenticated();
        $this->_serviceTemplate->setCurrentTheme($this->_session->get('themeCSS', 'default'));
		
		
        //Load the CSS files
        $this->_servicePage->appendCss('/css/' . $this->_serviceTemplate->getCurrentTheme() . ".bootstrap.min.css");
        $this->_servicePage->appendCss('/css/bootstrap-responsive.css');
        $this->_servicePage->appendCss('/css/rubedo.css');

        //load the javaScripts files
        $this->_servicePage->appendJs('/js/jquery.js');
        $this->_servicePage->appendJs('/js/bootstrap-transition.js');
        $this->_servicePage->appendJs('/js/bootstrap-alert.js');
        $this->_servicePage->appendJs('/js/bootstrap-modal.js');
        $this->_servicePage->appendJs('/js/bootstrap-dropdown.js');
        $this->_servicePage->appendJs('/js/bootstrap-scrollspy.js');
        $this->_servicePage->appendJs('/js/bootstrap-tab.js');
        $this->_servicePage->appendJs('/js/bootstrap-tooltip.js');
        $this->_servicePage->appendJs('/js/bootstrap-popover.js');
        $this->_servicePage->appendJs('/js/bootstrap-button.js');
        $this->_servicePage->appendJs('/js/bootstrap-collapse.js');
        $this->_servicePage->appendJs('/js/bootstrap-carousel.js');
        $this->_servicePage->appendJs('/js/bootstrap-typeahead.js');

        if ($isLoggedIn) {
            $this->_servicePage->appendJs('/ckeditor-dev/ckeditor.js');
        }

        $this->_servicePage->appendJs('/js/scripts.js');

        //find the page ID
        $calledUri = $this->getRequest()->getRequestUri();
        $pageId = $this->_serviceUrl->getPageId($calledUri);

        //build contents tree
        $this->_pageParams = $this->_getPageInfo($pageId);

        //Build Twig context
        $twigVar = $this->_pageParams;
        $twigVar["baseUrl"] = $this->getFrontController()->getBaseUrl();
        $twigVar['theme'] = $this->_serviceTemplate->getCurrentTheme();
        $twigVar['lang'] = $lang;
        $twigVar['title'] = $this->_servicePage->getPageTitle();
        $twigVar['css'] = $this->_servicePage->getCss();
        $twigVar['js'] = $this->_servicePage->getJs();
        $twigVar['isLoggedIn'] = $isLoggedIn;

        //Render content with template
        $content = $this->_serviceTemplate->render($this->_pageParams['template'], $twigVar);

        //disable ZF view layer
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getHelper('Layout')->disableLayout();

        //return the content
        $this->getResponse()->appendBody($content, 'default');

    }

    /**
     * Return page infos based on its ID
     *
     * @param string|int $pageId requested URL
     * @return array
     */
    protected function _getPageInfo($pageId) {

        $pageService = Manager::getService('Pages');
        $pageInfo = $pageService->findById($pageId);
        $this->_servicePage->setPageTitle($pageInfo['text']);
        $pageInfo['rows'] = $this->_getRowsInfos($pageInfo['rows']);
        $pageInfo['template'] = 'root/page.html';

        return $pageInfo;
    }

    protected function _getColumnsInfos(array $columns = null) {
        if ($columns === null) {
            return null;
        }
        $returnArray = $columns;
        foreach ($columns as $key => $column) {
            if (is_array($column['blocks'])) {
                $returnArray[$key]['blocks'] = $this->_getBlocksInfos($column['blocks']);
            } else {
                $returnArray[$key]['rows'] = $this->_getRowsInfos($column['rows']);
            }
        }
        return $returnArray;
    }

    protected function _getBlocksInfos(array $blocks) {
        $returnArray = array();
        foreach ($blocks as $block) {
            $returnArray[] = $this->_getBlockData($block);
        }
        return $returnArray;
    }

    protected function _getRowsInfos(array $rows = null) {
        if ($rows === null) {
            return null;
        }
        $returnArray = $rows;
        foreach ($rows as $key => $row) {
            if (is_array($row['columns'])) {
                $returnArray[$key]['columns'] = $this->_getColumnsInfos($row['columns']);
            }
        }
        return $returnArray;
    }

    /**
     * Return the data associated to a block given by config array
     * @param array $block bloc options (type, filter params...)
     * @return array block data to be rendered
     */
    protected function _getBlockData($block) {
        $params = array();
        switch($block['bType']) {
            case 'Bloc de navigation' :
                $controller = 'nav-bar';
                break;
            case 'Carrousel' :
                $controller = 'carrousel';
                break;
            case 'Liste de Contenus' :
                $controller = 'content-list';
                break;
            case 'Pied de page' :
                $controller = 'footer';
                break;
            case 'Résultat de recherche' :
                $controller = 'search';
                break;
            case 'Fil d\'Ariane' :
                $controller = 'breadcrumbs';
                break;
            case 'Twig' :
                if ($block['configBloc']['fileName'] == 'responsive') {
                    $controller = 'responsive';
                } else {
                    $controller = 'iframe';
                }

                break;
            case 'Détail de contenu' :
                $controller = 'content-single';
				$contentIdParam = $this->getRequest()->getParam('content-id');
                $contentId = $contentIdParam ? $contentIdParam : $block['configBloc']['contentId'];
                $params = array('content-id' => $contentId);
                break;
            default :
                $data = array();
                $template = 'root/block.html';
                return array('data' => $data, 'template' => $template);
                break;
        }

        $response = Action::getInstance()->action('index', $controller, 'blocks', $params);
        $data = $response->getBody('content');
        $template = $response->getBody('template');
        return array('data' => $data, 'template' => $template);

    }

}
