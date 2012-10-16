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
class IndexController extends AbstractController
{

	/**
	 * Current front office page parameters
	 * @var array
	 */
	protected $_pageParams = array();

	public function init(){
		parent::init();
		
		$calledUri = $this->getRequest()->getRequestUri();
		$serviceUrl =  Rubedo\Services\Manager::getService('Url');
		$this->_pageParams = $serviceUrl->getPageInfo($calledUri);		
	}

    /**
     * Main Action : render the Front Office view
     */
    public function indexAction()
    {
    }


}

