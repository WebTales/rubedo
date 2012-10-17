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

require_once (APPLICATION_PATH . '/controllers/AbstractController.php');

/**
 * Application initialization class
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	/**
	 * Init the DB info by setting static values in the DB class
	 */
	protected function _initMongoDataStream() {
		$options = $this->getOption('datastream');
		if(isset($options)) {
			$connectionString = 'mongodb://';
			if(!empty($options['mongo']['login'])) {
				$connectionString .= $options['mongo']['login'];
				$connectionString .= ':' . $options['mongo']['password'] . '@';
			}
			$connectionString .= $options['mongo']['server'];
			Rubedo\Mongo\DataAccess::setDefaultMongo($connectionString);

			Rubedo\Mongo\DataAccess::setDefaultDb($options['mongo']['db']);
		}
	}

	/**
	 * Load services parameter from application.ini to the service manager
	 */
	protected function _initServices() {
		$options = $this->getOption('services');
		if(isset($options)) {
			Rubedo\Services\Manager::setOptions($options);
		} else {
			$defaultArray = array('logLevel' => 3, 'enableCache' => 1);
			Rubedo\Services\Manager::setOptions($defaultArray);
		}
		$serviceOptions = Rubedo\Services\Manager::getOptions();
		/*define('LOG_LEVEL', $serviceOptions['logLevel']);
		 define('ENABLE_CACHE', $serviceOptions['enableCache']);*/

	}

	/**
	 * Load router configuration with specific rules
	 */
	protected function _initRouter() {
		$front = $this->bootstrap('FrontController')->getResource('FrontController');

		/**
		 * @var Zend_Controller_Router_Rewrite
		 */
		$router = $front->getRouter();

		//default front office route : should be called only if no module is specified
		$route = new Zend_Controller_Router_Route_Regex('(?:(?!backoffice|theme|lang|result|detail|javascritp|access).)+', array('controller' => 'index', 'action' => 'index'));
		$router->addRoute('rewrite', $route);

		//legacy json access. Should be removed when all store API had been updated
		$route = new Zend_Controller_Router_Route_Regex('backoffice/data/([a-zA-Z]*).json', array('controller' => 'data-access', 'action' => 'index', 'module' => 'backoffice'), array('1' => 'store'));
		$router->addRoute('json', $route);

		//static route to return app.js
		$route = new Zend_Controller_Router_Route_Static('backoffice/app.js', array('controller' => 'index', 'action' => 'appjs', 'module' => 'backoffice'));
		$router->addRoute('appjs', $route);

	}

}
