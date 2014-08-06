<?php

namespace RubedoAPI\Frontoffice\Controller;
use Rubedo\Services\Manager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Front Office Defautl Controller
 *
 * Invoked when calling front office URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ApiController extends AbstractActionController
{
    /**
     * Main Action : render the Front Office view
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

        $routes = $this->params()->fromRoute();
        $ressource = 'RubedoAPI\\Rest\\' . mb_strtoupper($routes['version']) . '\\' . ucfirst($routes['ressource']) . 'Ressource';
        $ressourceObject = new $ressource();
        $params = $this->params()->fromQuery();
        $result = $ressourceObject->{mb_strtolower($method)}($params);
        return new JsonModel($result);
    }
}
