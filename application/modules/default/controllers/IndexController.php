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
Use Rubedo\Controller\Action, Rubedo\Services\Manager;

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
     *
     */
    public function indexAction ()
    {
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        $httpProtocol = $isHttps ? 'HTTPS' : 'HTTP';
        
        // init service variables
        $this->_serviceUrl = Manager::getService('Url');
        $this->_servicePage = Manager::getService('PageContent');
        $this->_serviceTemplate = Manager::getService('FrontOfficeTemplates');
        $this->_session = Manager::getService('Session');
        
        $this->_pageId = $this->getRequest()->getParam('pageId');
        $this->_servicePage->setCurrentPage($this->_pageId);
        
        // if no page found, maybe installation isn't set
        if (! $this->_pageId) {
            $applicationOptions = $this->getFrontController()
                ->getParam('bootstrap')
                ->getApplication()
                ->getOptions();
            
            if (! isset($applicationOptions['installed']) || ! isset($applicationOptions['installed']['status']) || $applicationOptions['installed']['status'] !== 'finished') {
                $this->_helper->redirector->gotoUrl($this->_helper->url('index', 'index', 'install')); // redirect
                                                                                                           // to
                                                                                                           // install
                                                                                                           // tool
            }
            throw new \Rubedo\Exceptions\NotFound('No Page found');
        }
        $this->_pageInfo = Manager::getService('Pages')->findById($this->_pageId);
        $this->_site = Manager::getService('Sites')->findById($this->_pageInfo['site']);
        
        
        //ensure protocol is authorized for this site
        if (! is_array($this->_site['protocol']) || count($this->_site['protocol']) == 0) {
            throw new Rubedo\Exceptions\Server('Protocol is not set for current site');
        }
        
        if (! in_array($httpProtocol, $this->_site['protocol'])) {
            $this->_helper->redirector->gotoUrl(strtolower(array_pop($this->_site['protocol'])) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }
        
        // context
        $lang = $this->_session->get('lang', 'fr');
        $isLoggedIn = Manager::getService('CurrentUser')->isAuthenticated();
        if (! $isLoggedIn || !Manager::getService('Acl')->hasAccess('ui.backoffice')) {
            $isPreview = false;
        } else {
            $isPreview = $this->getRequest()->getParam('preview', false);
        }
        
        if ($isPreview) {
            $isLoggedIn = false;
            Manager::getService('Url')->disableNavigation();
            $simulatedTime = $this->getRequest()->getParam('preview_date', null);
            if (isset($simulatedTime)) {
                Manager::getService('CurrentTime')->setSimulatedTime($simulatedTime);
            }
            $isDraft = $this->getRequest()->getParam('preview_draft', null);
            if (isset($isDraft) && $isDraft === "true") {
                Zend_Registry::set('draft', true);
            } else {
                Zend_Registry::set('draft', false);
            }
        } else {
            Zend_Registry::set('draft', false);
        }
        
        // build contents tree
        $this->_pageParams = $this->_getPageInfo($this->_pageId);
        
        // Load the CSS files
        $this->_servicePage->appendCss('/templates/' . $this->_serviceTemplate->getFileThemePath('css/rubedo.css'));
        
        $canEdit = $isLoggedIn && Manager::getService('Acl')->hasAccess('write.frontoffice.contents');
        
        // load the javaScripts files
        if ($canEdit) {
            $this->_servicePage->appendJs('/components/webtales/ckeditor/ckeditor.js');
            $this->_servicePage->appendJs('/templates/' . $this->_serviceTemplate->getFileThemePath('js/rubedo-edit.js'));
        }
        
       
        
        $this->_servicePage->setCurrentSite($this->_pageParams["site"]);
        
        // Build Twig context
        $twigVar = $this->_pageParams;
        
        // change title & description if displaying a single content as main
        // content
        $directContentId = $this->getParam('content-id', false);
        if ($directContentId) {
            
            $singleContent = Manager::getService('Contents')->findById($directContentId, ! Zend_Registry::get('draft'), false);
            if ($singleContent) {
                $twigVar['contentId'] = $directContentId;
                $this->_servicePage->setPageTitle($singleContent['text']);
                $this->_servicePage->setDescription($singleContent['fields']['summary']);
            }
        }
        $twigVar['currentPage']=$this->_pageId;
        $twigVar['currentWorkspace']=$this->_pageInfo['workspace'];
        $twigVar['isDraft'] = Zend_Registry::get('draft');
        $twigVar["baseUrl"] = $this->getFrontController()->getBaseUrl();
        $twigVar['theme'] = $this->_serviceTemplate->getCurrentTheme();
        $twigVar['lang'] = $lang;
        $twigVar['prefixTitle'] = isset($this->_site['title']) && ! empty($this->_site['title']) ? $this->_site['title'] . ' - ' : '';
        $twigVar['title'] = $this->_servicePage->getPageTitle();
        
        // set metadata
        $description = $this->_servicePage->getDescription();
        if (empty($description)) {
            $description = $this->_site['description'];
        }
        $twigVar['description'] = $this->_servicePage->getDescription();
        
        $author = $this->_site['author'];
        if (empty($author)) {
            $author = $this->_servicePage->getAuthor();
        }
        $twigVar['author'] = $author;
        
        $keywords = $this->_servicePage->getKeywords();
        if (count($keywords) === 0) {
            $keywords = is_array($this->_site['keywords']) ? $this->_site['keywords'] : array();
        }
        if (is_array($keywords)) {
            $twigVar['keywords'] = implode(',', $keywords);
        }
        
        $twigVar['css'] = $this->_servicePage->getCss();
        $twigVar['js'] = $this->_servicePage->getJs();
        $twigVar['isLoggedIn'] = $isLoggedIn;
        $twigVar['canEdit'] = $canEdit;
        
        $pageTemplate = $this->_serviceTemplate->getFileThemePath($this->_pageParams['template']);
        
        // Render content with template
        $content = $this->_serviceTemplate->render($pageTemplate, $twigVar);
        
        // disable ZF view layer
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getHelper('Layout')->disableLayout();
        
        // return the content
        $this->getResponse()->appendBody($content, 'default');
    }

    public function versionAction ()
    {
        $versionArray = array(
            'Zend_Framework' => Zend_Version::VERSION
        );
        
        $this->_helper->json($versionArray);
    }

    public function testMailAction ()
    {
        $to = $this->getParam('to', null);
        if (is_null($to)) {
            throw new \Rubedo\Exceptions\User('Please, give an email adresse');
        }
        $message = Manager::getService('Mailer')->getNewMessage();
        
        $message->setSubject('Rubedo Test Mail');
        $message->setReplyTo(array(
            'rubedo@webtales.fr' => 'Rubedo'
        ));
        $message->setReturnPath('jbourdin@gmail.com');
        $message->setFrom(array(
            'jbourdin@gmail.com'
        ));
        $message->setTo(array(
            $to
        ));
        
        $this->view->logo = $message->embed(Swift_Image::fromPath(APPLICATION_PATH . '/../vendor/webtales/rubedo-backoffice-ui/www/resources/images/logoRubedo.png'));
        $this->view->To = $to;
        // Set body content
        $msgContent = $this->view->render('index/mail.phtml');
        
        // Set the body
        $message->setBody($msgContent, 'text/html');
        
        $send = Manager::getService('Mailer')->sendMessage($message);
        if ($send == 0) {
            throw new \Rubedo\Exceptions\Server('No mail has been sent !');
        }
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
        $this->_mask = Manager::getService('Masks')->findById($this->_pageInfo['maskId']); // maskId
        if (! $this->_mask) {
            throw new \Rubedo\Exceptions\Server('no mask found');
        }
        
        $this->_currentContent = $this->getParam('content-id', null);
        
        // @todo get main column
        if ($this->_currentContent) {
            $this->_mainCol = $this->_getMainColumn();
        }
        
        $this->_blocksArray = array();
        foreach ($this->_mask['blocks'] as $block) {
            if (! isset($block['orderValue'])) {
                throw new \Rubedo\Exceptions\Server('no orderValue for block ' . $block['id']);
            }
            $this->_blocksArray[$block['parentCol']][$block['orderValue']] = $block;
        }
        foreach ($this->_pageInfo['blocks'] as $block) {
            if (! isset($block['orderValue'])) {
                throw new \Rubedo\Exceptions\Server('no orderValue for block ' . $block['id']);
            }
            $this->_blocksArray[$block['parentCol']][$block['orderValue']] = $block;
        }
        if ($this->_mainCol) {
            unset($this->_blocksArray[$this->_mainCol]);
            $this->_blocksArray[$this->_mainCol][] = $this->_getSingleBlock();
        }
        
        $this->_pageInfo['rows'] = $this->_mask['rows'];
        
        if (! isset($this->_site['theme'])) {
            $this->_site['theme'] = 'default';
        }
        $this->_serviceTemplate->setCurrentTheme($this->_site['theme']);
        
        $this->_servicePage->setPageTitle($this->_pageInfo['title']);
        $this->_servicePage->setDescription($this->_pageInfo['description']);
        $this->_servicePage->setKeywords($this->_pageInfo['keywords']);
        
        $rootline = Manager::getService('Pages')->getAncestors($this->_pageInfo);
        $this->_rootlineArray = array();
        foreach ($rootline as $ancestor) {
            $this->_rootlineArray[] = $ancestor['id'];
        }
        $this->_rootlineArray[] = $pageId;
        $this->_pageInfo['rows'] = $this->_getRowsInfos($this->_pageInfo['rows']);
        $this->_pageInfo['template'] = 'page.html.twig';
        
        return $this->_pageInfo;
    }

    protected function _getSingleBlock ()
    {
        $block = array();
        $block['configBloc'] = array();
        $block['bType'] = 'contentDetail';
        $block['id'] = 'single';
        $block['responsive'] = array(
            'tablet' => true,
            'desktop' => true,
            'phone' => true
        );
        
        return $block;
    }

    protected function _getMainColumn ()
    {
        return isset($this->_mask['mainColumnId']) ? $this->_mask['mainColumnId'] : null;
    }

    /**
     * get Columns infos
     *
     * @param array $columns            
     * @return array
     */
    protected function _getColumnsInfos (array $columns = null, $noSpan = false)
    {
        if ($columns === null) {
            return null;
        }
        $returnArray = $columns;
        foreach ($columns as $key => $column) {
            if ($noSpan) {
                $returnArray[$key]['span'] = null;
            }
            $returnArray[$key]['displayTitle'] = isset($column['displayTitle']) ? $column['displayTitle'] : null;
            $returnArray[$key]['elementTag'] = isset($column['elementTag']) ? $column['elementTag'] : null;
            $returnArray[$key]['elementStyle'] = isset($column['elementStyle']) ? $column['elementStyle'] : null;
            $returnArray[$key]['renderSpan'] = isset($column['renderSpan']) ? $column['renderSpan'] : true;
            $returnArray[$key]['template'] = Manager::getService('FrontOfficeTemplates')->getFileThemePath('column.html.twig');
            $returnArray[$key]['classHtml'] = isset($column['classHTML']) ? $column['classHTML'] : null;
            $returnArray[$key]['classHtml'] .= $this->_buildResponsiveClass($column['responsive']);
            $returnArray[$key]['idHtml'] = isset($column['idHTML']) ? $column['idHTML'] : null;
            if (isset($this->_blocksArray[$column['id']])) {
                $returnArray[$key]['blocks'] = $this->_getBlocksInfos($this->_blocksArray[$column['id']]);
                $returnArray[$key]['rows'] = null;
            } else {
                $returnArray[$key]['rows'] = $this->_getRowsInfos($column['rows']);
                $returnArray[$key]['blocks'] = null;
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
            $returnArray[$key]['displayTitle'] = isset($row['displayTitle']) ? $row['displayTitle'] : null;
            $returnArray[$key]['template'] = Manager::getService('FrontOfficeTemplates')->getFileThemePath('row.html.twig');
            $returnArray[$key]['classHtml'] = isset($row['classHTML']) ? $row['classHTML'] : null;
            $returnArray[$key]['classHtml'] .= $this->_buildResponsiveClass($row['responsive']);
            $returnArray[$key]['idHtml'] = isset($row['idHTML']) ? $row['idHTML'] : null;
            $returnArray[$key]['elementTag'] = isset($row['elementTag']) ? $row['elementTag'] : null;
            $returnArray[$key]['elementStyle'] = isset($row['elementStyle']) ? $row['elementStyle'] : null;
            $returnArray[$key]['displayRow'] = isset($row['displayRow']) ? $row['displayRow'] : true;
            $returnArray[$key]['displayRowFluid'] = isset($row['displayRowFluid']) ? $row['displayRowFluid'] : false;
            $returnArray[$key]['includeContainer'] = isset($row['includeContainer']) ? $row['includeContainer'] : false;
            $returnArray[$key]['includeContainerFluid'] = isset($row['includeContainerFluid']) ? $row['includeContainerFluid'] : false;
            $returnArray[$key]['containerId'] = isset($row['containerId']) ? $row['containerId'] : false;
            $returnArray[$key]['containerClass'] = isset($row['containerClass']) ? $row['containerClass'] : false;
            
            if (is_array($row['columns'])) {
                $noSpan = (isset($row['displayAsTab'])) ? $row['displayAsTab'] : false;
                $returnArray[$key]['columns'] = $this->_getColumnsInfos($row['columns'], $noSpan);
            } else {
                $returnArray[$key]['columns'] = null;
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
        $params['prefix'] = (isset($block['urlPrefix']) && ! empty($block['urlPrefix'])) ? $block['urlPrefix'] : $block['id'];
        $params['classHtml'] = isset($block['classHTML']) ? $block['classHTML'] : null;
        $params['classHtml'] .= $this->_buildResponsiveClass($block['responsive']);
        $params['elementTag'] = isset($block['elementTag']) ? $block['elementTag'] : null;
        $params['elementStyle'] = isset($block['elementStyle']) ? $block['elementStyle'] : null;
        $params['renderDiv'] = isset($block['renderDiv']) ? $block['renderDiv'] : true;
        $params['idHtml'] = isset($block['idHTML']) ? $block['idHTML'] : null;
        $params['displayTitle'] = isset($block['displayTitle']) ? $block['displayTitle'] : false;
        $params['blockTitle'] = isset($block['title']) ? $block['title'] : null;
        $params['current-page'] = $this->_pageId;
        
        $blockQueryParams = $this->getRequest()->getParam($params['prefix'], array());
        foreach ($blockQueryParams as $key => $value) {
            $params[$key] = $value;
        }
        
        switch ($block['bType']) {
            case 'Bloc de navigation':
            case 'navigation':
                $controller = 'nav-bar';
                $params['currentPage'] = $this->_pageId;
                $params['rootline'] = $this->_rootlineArray;
                $params['rootPage'] = $this->_serviceUrl->getPageId('accueil', $this->getRequest()
                    ->getHttpHost());
                
                break;
            case 'Carrousel':
            case 'carrousel':
                $controller = 'carrousel';
                break;
            case 'googleMaps':
                $controller = 'google-maps';
                break;
            case 'Gallerie Flickr':
            case 'flickrGallery':
                $controller = 'flickr-gallery';
                break;
            case 'Liste de Contenus':
            case 'contentList':
                $controller = 'content-list';
                break;
             case 'Formulaire':
             case 'form':
                	$controller = 'forms';
                	break;
            case 'calendar':
                $controller = 'calendar';
                break;
            case 'Pied de page':
            case 'footer':
                $controller = 'footer';
                break;
            case 'Résultat de recherche':
            case 'searchResults':
                $params['constrainToSite'] = $block['configBloc']['constrainToSite'];
                $controller = 'search';
                
                break;
            case 'geoSearchResults':
                $params['constrainToSite'] = $block['configBloc']['constrainToSite'];
                $controller = 'geo-search';
                
                break;
               
            case 'Fil d\'Ariane':
            case 'breadcrumb':
                $params['currentPage'] = $this->_pageId;
                $params['rootline'] = $this->_rootlineArray;
                $controller = 'breadcrumbs';
                break;
            case 'searchForm':
                $controller = 'search-form';
                break;
            case 'Twig':
            case 'twig':
                $controller = 'twig';
                $params['template'] = $block['configBloc']['fileName'];
                
                break;
            case 'Détail de contenu':
            case 'contentDetail':
                $controller = 'content-single';
                $contentIdParam = $this->getRequest()->getParam('content-id');
                $contentId = $contentIdParam ? $contentIdParam : null;
                if (! isset($contentId)) {
                    $contentId = isset($block['configBloc']['contentId']) ? $block['configBloc']['contentId'] : null;
                }
                
                $params['content-id'] = $contentId;
                
                break;
            case 'Média externe':
            case 'externalMedia':
                $controller = 'embedded-media';
                break;
            case 'Image':
            case 'image':
                $controller = 'image';
                break;
            case 'Audio':
            case 'audio':
                $controller = 'audio';
                break;
            case 'Video':
            case 'video':
                $controller = 'video';
                break;
            case 'Authentication':
            case 'authentication':
                $controller = 'authentication';
                break;
            case 'Texte':
            case 'simpleText':
                $controller = 'text';
                break;
            case 'imageGallery':
                $controller = 'gallery';
                break;
            case 'Texte Riche':
            case 'richText':
                $controller = 'rich-text';
                break;
            case 'AddThis':
            case 'addThis':
                $controller = 'addthis';
                break;
            case 'AddThisFollow':
            case 'addThisFollow':
                $controller = 'addthisfollow';
                break;
            case 'Menu':
            case 'menu':
                $controller = 'menu';
                break;
            case 'Controleur Zend':
            case 'zendController':
                $module = isset($block['configBloc']['module']) ? $block['configBloc']['module'] : 'blocks';
                $controller = isset($block['configBloc']['controller']) ? $block['configBloc']['controller'] : null;
                $action = isset($block['configBloc']['action']) ? $block['configBloc']['action'] : null;
                
                $route = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRoute();
                $prefix = (isset($block['urlPrefix']) && ! empty($block['urlPrefix'])) ? $block['urlPrefix'] : $block['id'];
                $route->setPrefix($prefix);
                
                $allParams = $this->getAllParams();
                foreach ($allParams as $key => $value) {
                    $prefixPos = strpos($key, $prefix . '_');
                    if ($prefixPos === 0) {
                        $subKey = substr($key, strlen($prefix . '_'));
                        switch ($subKey) {
                            case 'action':
                                $action = $value;
                                break;
                            case 'controller':
                                $controller = $value;
                                break;
                            case 'module':
                                $module = $value;
                                break;
                            default:
                                $params[$subKey] = $value;
                                break;
                        }
                    } else {
                        $params[$key] = $value;
                    }
                }
                
                $response = Action::getInstance()->action($action, $controller, $module, $params);
                $route->clearPrefix();
                $data = $response->getBody();
                
                return array(
                    'data' => array(
                        'content' => $data
                    ),
                    'template' => 'root/zend.html.twig'
                );
                break;
            case 'Contact':
            case 'contact':
            	$controller = "contact";
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
