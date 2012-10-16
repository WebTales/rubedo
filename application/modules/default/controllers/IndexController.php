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
class IndexController extends AbstractController {

	/**
	 * Current front office page parameters
	 * @var array
	 */
	protected $_pageParams = array();

	public function init() {
		parent::init();

		$this->_serviceUrl = Rubedo\Services\Manager::getService('Url');
		$this->_serviceTemplate = Rubedo\Services\Manager::getService('FrontOfficeTemplates');
		
		$defaultNamespace = new Zend_Session_Namespace('Default');
		$lang = $defaultNamespace->lang;
		$this->_serviceTemplate->init($lang);

	}

	/**
	 * Main Action : render the Front Office view
	 */
	public function indexAction() {
		
		
		$this->getHelper('ViewRenderer')->setNoRender();
		$this->getHelper('Layout')->disableLayout();

		$calledUri = $this->getRequest()->getRequestUri();
		$this->_pageParams = $this->_serviceUrl->getPageInfo($calledUri);
		
		$defaultNamespace = new Zend_Session_Namespace('Default');
		$lang = $defaultNamespace->lang;
		
		
		$twigVar = array();
		$twigVar['theme'] = $defaultNamespace->themeCSS;
		$twigVar['lang'] = $lang;
		
		foreach($this->_pageParams['blocks'] as $block) {
			$helper= 'helper'.$block['Module'];
			$output = $block['Output'];
			$input = $block['Input'];
			$twigVar[$output] = $this->_helper->$helper($input);
		}
		
		$content = $this->_serviceTemplate->render($this->_pageParams['template'], $twigVar);
	
		$this->getResponse()->appendBody($content, 'default');

	}

}
