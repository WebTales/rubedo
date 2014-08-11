<?php

namespace RubedoAPI\Frontoffice\Controller;
use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIAbstractException;
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
        try {
            $routes = $this->params()->fromRoute();
            array_walk(
                $routes['api'],
                function (&$item, $key)
                {
                    $item = ucfirst($item);
                }
            );
            $class = array_pop($routes['api']). 'Ressource';
            $ressource = 'RubedoAPI\\Rest\\' . mb_strtoupper($routes['version']) . '\\';
            if (!empty($routes['api']))
                $ressource .= implode('\\', $routes['api']) . '\\';
            $ressource .= $class ;
            /** @var \RubedoAPI\Interfaces\IRessource $ressourceObject */
            $ressourceObject = new $ressource();

            $paramsBody = json_decode($this->getRequest()->getContent(), true);
            if (empty($paramsBody))
                $paramsBody = array();
            $params = array_merge_recursive(
                $this->params()->fromQuery(),
                $paramsBody
            );
            $ressourceObject->setContext($this);
            $result = $ressourceObject->handler(mb_strtolower($method), $params);
        } catch(APIAbstractException $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
            $this->getResponse()->setStatusCode($e->getHttpCode());
        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
            $this->getResponse()->setStatusCode($e->getCode());
        }
        return new JsonModel($result);
    }
}
