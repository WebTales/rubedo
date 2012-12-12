<?php

/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Router;

Use Rubedo\Services\Manager;

/**
 * Zend_Controller_Router_Route implementation for frontend pages
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Route extends \Zend_Controller_Router_Route_Abstract implements \Zend_Controller_Router_Route_Interface {
	/**
	 * Request Values
	 * 
	 * @var array
	 */
	protected $_values = array ();
	
	/**
	 * Instantiates route based on passed Zend_Config structure
	 */
	public static function getInstance(\Zend_Config $config) {
		$frontController = \Zend_Controller_Front::getInstance ();
		
		$defs = array ();
		$dispatcher = $frontController->getDispatcher ();
		$request = $frontController->getRequest ();
		
		return new self ( $defs, $dispatcher, $request );
	}
	
	/**
	 * Assembles user submitted parameters forming a URL path defined by this
	 * route
	 *
	 * @param array $data
	 *        	An array of variable and value pairs used as parameters
	 * @param bool $reset
	 *        	Weither to reset the current params
	 * @return string Route path with user submitted parameters
	 */
	public function assemble($data = array(), $reset = false, $encode = false) {
		// TODO Auto-generated method stub
		$params = (!$reset) ? $this->_values : array();
		foreach ($data as $key => $value) {
			if ($value !== null) {
				$params[$key] = $value;
			} elseif (isset($params[$key])) {
				unset($params[$key]);
			}
		}
		$url = '';
		
		return ltrim($url, self::URI_DELIMITER);
		
	}
	
	/**
	 * Matches a user submitted path.
	 * Assigns and returns an array of variables
	 * on a successful match.
	 *
	 * If a request object is registered, it uses its setModuleName(),
	 * setControllerName(), and setActionName() accessors to set those values.
	 * Always returns the values as an array.
	 *
	 * @param string $path
	 *        	Path used to match against this routing map
	 * @return array An array of assigned values or a false on a mismatch
	 */
	public function match($path) {
		$pageId = Manager::getService ( 'Url' )->getPageId ( $path->getRequestUri () );
		if ($pageId === null) {
			return false;
		} else {
			$this->_values = array (
					'controller' => 'index',
					'action' => 'index',
					'pageId' => $pageId 
			);
			return $this->_values;
		}
	}
}

?>