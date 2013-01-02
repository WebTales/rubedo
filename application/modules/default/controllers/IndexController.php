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
     * array of parent IDs
     */
    protected $_rootlineArray;

    /**
     * Main Action : render the Front Office view
     *
     * @todo remove test
     */
    public function indexAction ()
    {
        // init service variables
        $this->_serviceUrl = Manager::getService('Url');
        $this->_servicePage = Manager::getService('PageContent');
        $this->_serviceTemplate = Manager::getService('FrontOfficeTemplates');
        $this->_session = Manager::getService('Session');
        
        // context
        $lang = $this->_session->get('lang', 'fr');
        $isLoggedIn = Manager::getService('CurrentUser')->isAuthenticated();
        if (! $isLoggedIn) {
            $isPreview = false;
        } else {
            $isPreview = $this->getRequest()->getParam('preview', false);
        }
        
        if ($isPreview) {
            $isLoggedIn = false;
            Manager::getService('Url')->disableNavigation();
            $simulatedTime = $this->getRequest()->getParam('preview_time', null);
            if (isset($simulatedTime)) {
                Manager::getService('CurrentTime')->setSimulatedTime($simulatedTime);
            }
            $isDraft = $this->getRequest()->getParam('preview_draft', null);
            if (isset($isDraft)) {
                Zend_Registry::set('draft', true);
            } else {
                Zend_Registry::set('draft', false);
            }
        } else {
            Zend_Registry::set('draft', false);
        }
        
        // $this->_serviceTemplate->setCurrentTheme();
        
        // Load the CSS files
        
        // load the javaScripts files
        if ($isLoggedIn) {
            $this->_servicePage->appendJs('/ckeditor-dev/ckeditor.js');
        }
        
        $this->_servicePage->appendJs('/js/scripts.js');
        
        $this->_pageId = $this->getRequest()->getParam('pageId');
        
        // build contents tree
        $this->_pageParams = $this->_getPageInfo($this->_pageId);
        $this->_servicePage->setCurrentSite($this->_pageParams["site"]);
        
        // Build Twig context
        $twigVar = $this->_pageParams;
        $twigVar["baseUrl"] = $this->getFrontController()->getBaseUrl();
        $twigVar['theme'] = $this->_serviceTemplate->getCurrentTheme();
        $twigVar['lang'] = $lang;
        $twigVar['title'] = $this->_servicePage->getPageTitle();
        $twigVar['css'] = $this->_servicePage->getCss();
        $twigVar['js'] = $this->_servicePage->getJs();
        $twigVar['isLoggedIn'] = $isLoggedIn;
        
        $pageTemplate = $this->_serviceTemplate->getFileThemePath($this->_pageParams['template']);
        
        // Render content with template
        $content = $this->_serviceTemplate->render($pageTemplate, $twigVar);
        
        // disable ZF view layer
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getHelper('Layout')->disableLayout();
        
        // return the content
        $this->getResponse()->appendBody($content, 'default');
    }

    /**
     * Return page infos based on its ID
     *
     * @param string|int $pageId
     *            requested URL
     * @return array
     */
    protected function _getPageInfo ($pageId)
    {
        $pageService = Manager::getService('Pages');
        $pageInfo = $pageService->findById($pageId);
        
        $this->_site = Manager::getService('Sites')->findById($pageInfo['site']);
        if (! isset($this->_site['theme'])) {
            $this->_site['theme'] = 'default';
        }
        $this->_serviceTemplate->setCurrentTheme($this->_site['theme']);
        
        $this->_servicePage->setPageTitle($pageInfo['text']);
        $rootline = $pageService->getAncestors($pageInfo);
        $this->_rootlineArray=array();
        foreach ($rootline as $ancestor){
            $this->_rootlineArray[]=$ancestor['id'];
        }
        $this->_rootlineArray[] = $pageId;
        $pageInfo['rows'] = $this->_getRowsInfos($pageInfo['rows']);
        $pageInfo['template'] = 'page.html.twig';
        
        return $pageInfo;
    }

    /**
     * get Columns infos
     * 
     * @param array $columns
     * @return array
     */
    protected function _getColumnsInfos (array $columns = null)
    {
        if ($columns === null) {
            return null;
        }
        $returnArray = $columns;
        foreach ($columns as $key => $column) {
            $returnArray[$key]['template'] = Manager::getService('FrontOfficeTemplates')->getFileThemePath('column.html.twig');
            $returnArray[$key]['classHtml'] = $column['classHTML'];
            $returnArray[$key]['classHtml'] .= $this->_buildResponsiveClass($column['responsive']);
            $returnArray[$key]['idHtml'] = $column['idHTML'];
            
            if (is_array($column['blocks'])) {
                $returnArray[$key]['blocks'] = $this->_getBlocksInfos($column['blocks']);
            } else {
                $returnArray[$key]['rows'] = $this->_getRowsInfos($column['rows']);
            }
        }
        return $returnArray;
    }

    /**
     * get Blocks infos
     *
     * @param array $blocks
     * @return array
     */
    protected function _getBlocksInfos (array $blocks)
    {
        $returnArray = array();
        foreach ($blocks as $block) {
            $returnArray[] = $this->_getBlockData($block);
        }
        return $returnArray;
    }

    
    /**
     * get Rows infos
     * 
     * @param array $rows
     * @return array
     */
    protected function _getRowsInfos (array $rows = null)
    {
        if ($rows === null) {
            return null;
        }
        $returnArray = $rows;
        foreach ($rows as $key => $row) {
            $returnArray[$key]['template'] = Manager::getService('FrontOfficeTemplates')->getFileThemePath('row.html.twig');
            $returnArray[$key]['classHtml'] = $row['classHTML'];
            $returnArray[$key]['classHtml'] .= $this->_buildResponsiveClass($row['responsive']);
            $returnArray[$key]['idHtml'] = $row['idHTML'];
            
            if (is_array($row['columns'])) {
                $returnArray[$key]['columns'] = $this->_getColumnsInfos($row['columns']);
            }
        }
        return $returnArray;
    }

    /**
     * Return the data associated to a block given by config array
     *
     * @param array $block
     *            bloc options (type, filter params...)
     * @return array block data to be rendered
     */
    protected function _getBlockData ($block)
    {
        $params = array();
        $params['block-config'] = $block['configBloc'];
        $params['site'] = $this->_site;
        $params['blockId'] = $block['id'];
        $params['prefix'] = isset($block['urlPrefix']) ? $block['urlPrefix'] : $block['id'];
        $params['classHtml'] = $block['classHTML'];
        $params['classHtml'] .= $this->_buildResponsiveClass($block['responsive']);
        $params['idHtml'] = $block['idHTML'];
        
        $blockQueryParams = $this->getRequest()->getParam($params['prefix'], array());
        foreach ($blockQueryParams as $key => $value) {
            $params[$key] = $value;
        }
        
        switch ($block['bType']) {
            case 'Bloc de navigation':
                $controller = 'nav-bar';
                $params['currentPage'] = $this->_pageId;
                $params['rootline'] = $this->_rootlineArray;
                $params['rootPage'] = $this->_serviceUrl->getPageId('accueil', $this->getRequest()
                    ->getHttpHost());
                
                break;
            case 'Carrousel':
                $controller = 'carrousel';
                break;
            case 'Liste de Contenus':
                $controller = 'content-list';
                
                break;
            case 'Pied de page':
                $controller = 'footer';
                break;
            case 'Résultat de recherche':
                $controller = 'search';
                break;
            case 'Fil d\'Ariane':
                $params['rootline'] = $this->_rootlineArray;
                $controller = 'breadcrumbs';
                break;
            case 'Twig':
                $controller = 'twig';
                $params['template'] = $block['configBloc']['fileName'];
                
                break;
            case 'Détail de contenu':
                $controller = 'content-single';
                $contentIdParam = $this->getRequest()->getParam('content-id');
                $contentId = $contentIdParam ? $contentIdParam : null;
                if (! isset($contentId)) {
                    $contentId = isset($block['configBloc']['contentId']) ? $block['configBloc']['contentId'] : null;
                }
                
                $params = array(
                    'content-id' => $contentId
                );
                break;
            default:
                $data = array();
                $template = 'root/block.html';
                return array(
                    'data' => $data,
                    'template' => $template
                );
                break;
        }
        
        $response = Action::getInstance()->action('index', $controller, 'blocks', $params);
        $data = $response->getBody('content');
        $template = $response->getBody('template');
        return array(
            'data' => $data,
            'template' => $template
        );
    }

    protected function _buildResponsiveClass ($responsiveArray)
    {
        foreach ($responsiveArray as $key => $value) {
            if (false == $value) {
                unset($responsiveArray[$key]);
            }
        }
        
        $responsiveArray = array_keys($responsiveArray);
        
        switch (count($responsiveArray)) {
            case 3:
                $class = '';
                break;
            case 2:
                $hiddenArray = array(
                    'tablet',
                    'desktop',
                    'phone'
                );
                list ($hiddenMedia) = array_values(array_diff($hiddenArray, $responsiveArray));
                
                $class = ' hidden-' . $hiddenMedia;
                break;
            case 0:
                $class = ' hidden';
                break;
            case 1:
            default:
                $class = '';
                foreach ($responsiveArray as $value) {
                    $class .= ' visible-' . $value;
                }
                break;
        }
        return $class;
    }
}
