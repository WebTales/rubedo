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

        $twigVar = array();
        $twigVar['theme'] = $session->get('themeCSS', 'default');
        $twigVar['lang'] = $lang;

        foreach ($this->_pageParams['blocks'] as $block) {
            $twigVar = array_merge($twigVar, $this->_serviceBlock->getBlockData($block, $pageId, $this));
        }

        if ($pageId == "newpage") {
            $newTwigVar['structure'] = array();
            $newTwigVar['structure'][] = array('nested' => array(1 => array('params' => array('span' => 9), 'nested' => array( array('template' => 'root/blocks/headline.html', 'data' => array()))), 2 => array('params' => array('span' => 3), 'nested' => array( array('template' => 'root/blocks/carrousel.html', 'data' => array('items' => $twigVar['carousel_content']))))));
            $newTwigVar['structure'][] = array('nested' => array(1 => array('params' => array('span' => 12), 'nested' => array( array('template' => 'root/blocks/contentlist.html', 'data' => array('contents' => $twigVar['contentlist_content'])))), ));
			
			$newTwigVar['theme'] = $twigVar['theme'];
			
            /*
             $newTwigVar['structure'] = array(
             array('template'=>'root/blocks/carrousel.html','data'=>array('items'=>$twigVar['carousel_content'])),
             array('template'=>'root/blocks/contentlist.html','data'=>array('contents'=>$twigVar['contentlist_content'])),
             );

             */
            $twigVar = $newTwigVar;
        }

        //Zend_Debug::dump($twigVar,$this->_pageParams['template']);
        //die();

        $content = $this->_serviceTemplate->render($this->_pageParams['template'], $twigVar);

        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getHelper('Layout')->disableLayout();

        $this->getResponse()->appendBody($content, 'default');

    }

    /**
     * @todo delete this ASAP : use model class instead of HELPERS !!!
     */
    public function getProtectedHelper() {
        return $this->_helper;
    }

}
