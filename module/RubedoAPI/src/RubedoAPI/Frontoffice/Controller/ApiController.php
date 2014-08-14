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
        $method = null;
        if (method_exists($this->getRequest(), 'getMethod')) {
            $method = $this->getRequest()->getMethod();
        } else {
            return new JsonModel([
                'success' => false,
                'message' => 'Method not exist',
            ]);
        }
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
            $params = array_merge_recursive(
                $this->getRequest()->getServer()->toArray(),
                $this->params()->fromQuery(),
                $this->params()->fromPost(),
                $paramsBody
            );
            $ressourceObject->setContext($this);
            if (isset($routes['id']))
                return new JsonModel($ressourceObject->handlerEntity($routes['id'], mb_strtolower($method), $params));
            return new JsonModel($ressourceObject->handler(mb_strtolower($method), $params));
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
