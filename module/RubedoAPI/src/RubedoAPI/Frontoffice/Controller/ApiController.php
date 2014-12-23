<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Frontoffice\Controller;

use Rubedo\Collection\AbstractCollection;
use RubedoAPI\Exceptions\APIAbstractException;
use RubedoAPI\Exceptions\APIRequestException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Class ApiController
 *
 * @package RubedoAPI\Frontoffice\Controller
 */
class ApiController extends AbstractActionController
{
    /**
     * Entry point for API
     *
     * @internal Check if the resource exist (with namespace), and call handler.
     * @return JsonModel
     */
    public function indexAction()
    {
        AbstractCollection::setIsFrontEnd(true);
        try {
            $routes = $this->params()->fromRoute();
            array_walk(
                $routes['api'],
                function (&$item) {
                    $item = ucfirst($item);
                }
            );
            $namespacesToSearch = array();

            $serviceLocator = $this->getServiceLocator();

            $loadedModules = $serviceLocator ? $serviceLocator->get('ModuleManager')->getLoadedModules() : array();

            foreach ($loadedModules as $module) {
                $moduleConfig = $module->getConfig();
                $namespacesToSearch = array_merge($namespacesToSearch, isset($moduleConfig['namespaces_api']) ? $moduleConfig['namespaces_api'] : array());
            }
            $resourceArray = array('Rest', mb_strtoupper($routes['version']));
            $class = array_pop($routes['api']) . 'Resource';
            if (!empty($routes['api'])) {
                $resourceArray = array_merge($resourceArray, $routes['api']);
            }
            $resourceArray[] = $class;
            foreach (array_reverse($namespacesToSearch) as $namespaceWithRest) {
                /** @var \RubedoAPI\Interfaces\IResource $resourceObject */
                $namespacedResource = implode('\\', array_merge(array($namespaceWithRest), $resourceArray));
                if (class_exists($namespacedResource)) {
                    $resourceObject = new $namespacedResource();
                    break;
                }
            }
            if (!isset($resourceObject)) {
                throw new APIRequestException('Resource not exist', 404);
            }

            $paramsBody = json_decode($this->getRequest()->getContent(), true);
            if (empty($paramsBody))
                $paramsBody = array();
            if (!empty($_SERVER['HTTP_AUTHORIZATION']))
                list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
            $params = array_replace_recursive(
                $this->getRequest()->getServer()->toArray(),
                $routes,
                $this->params()->fromQuery(),
                $this->params()->fromPost(),
                $this->params()->fromFiles(),
                $paramsBody
            );
            $resourceObject->setContext($this);
            $method = null;
            if (isset($routes['method'])) {
                $method = mb_strtolower($routes['method']);
            } elseif (method_exists($this->getRequest(), 'getMethod')) {
                $method = mb_strtolower($this->getRequest()->getMethod());
            } else {
                return new JsonModel([
                    'success' => false,
                    'message' => 'Method not exist',
                ]);
            }
            if (isset($routes['id']))
                return new JsonModel($resourceObject->handlerEntity($routes['id'], $method, $params));
            return new JsonModel($resourceObject->handler($method, $params));
        } catch (APIAbstractException $e) {
            $this->getResponse()->setStatusCode($e->getHttpCode());
            return new JsonModel(
                array(
                    'success' => false,
                    'message' => $e->getMessage(),
                )
            );
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(500);
            return new JsonModel(
                array(
                    'success' => false,
                    'message' => $e->getMessage(),
                )
            );
        }
    }
}
