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
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Frontoffice\Controller;

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
     * @internal Check if the ressource exist (with namespace), and call handler.
     * @return JsonModel
     */
    public function indexAction()
    {
        try {
            $routes = $this->params()->fromRoute();
            array_walk(
                $routes['api'],
                function (&$item, $key) {
                    $item = ucfirst($item);
                }
            );
            $class = array_pop($routes['api']) . 'Ressource';
            $ressource = 'RubedoAPI\\Rest\\' . mb_strtoupper($routes['version']) . '\\';
            if (!empty($routes['api']))
                $ressource .= implode('\\', $routes['api']) . '\\';
            $ressource .= $class;
            /** @var \RubedoAPI\Interfaces\IRessource $ressourceObject */
            if (!class_exists($ressource)) {
                throw new APIRequestException('Ressource not exist', 404);
            }
            $ressourceObject = new $ressource();

            $paramsBody = json_decode($this->getRequest()->getContent(), true);
            if (empty($paramsBody))
                $paramsBody = array();
            if (!empty($_SERVER['HTTP_AUTHORIZATION']))
                list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
            $params = array_merge_recursive(
                $this->getRequest()->getServer()->toArray(),
                $routes,
                $this->params()->fromQuery(),
                $this->params()->fromPost(),
                $this->params()->fromFiles(),
                $paramsBody
            );
            $ressourceObject->setContext($this);
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
                return new JsonModel($ressourceObject->handlerEntity($routes['id'], $method, $params));
            return new JsonModel($ressourceObject->handler($method, $params));
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
