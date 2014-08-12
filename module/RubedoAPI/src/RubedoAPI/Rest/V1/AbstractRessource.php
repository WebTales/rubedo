<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 06/08/14
 * Time: 13:47
 */

namespace RubedoAPI\Rest\V1;


use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIControllerException;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Interfaces\IRessource;
use RubedoAPI\Tools\DefinitionEntity;

/**
 * Class AbstractRessource
 * @package RubedoAPI\Rest\V1
 * @method \RubedoAPI\Collection\UserTokens getUserTokensAPICollection() Return UserTokens collection
 * @method \RubedoAPI\Services\Security\Authentication getAuthAPIService() Return Authentication service
 */
abstract class AbstractRessource implements IRessource {
    protected $config = [];
    protected $context;
    protected $callCache = array();
    /**
     * @var \RubedoAPI\Tools\DefinitionEntity
     */
    protected $definition;
    function __construct()
    {
        $this->definition = new DefinitionEntity();
    }

    protected  function getConfig()
    {
        Manager::getService('Config');
        return $this->config;
    }

    protected function addToConfig($config)
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    public function optionsAction()
    {
        return $this->definition->jsonSerialize();
    }

    public function getDefinition()
    {
        return $this->definition;
    }

    public function handler($method, $params)
    {
        if (!method_exists($this, $method . 'Action'))
            throw new APIRequestException('Verb not implemented', 500);
        if ($method == 'options')
            return $this->optionsAction();
        $verbDefinition = $this->getDefinition()->getVerb($method);

        return $verbDefinition->filterOutput(
            $this->{$method . 'Action'}(
                $verbDefinition->filterInput($params)
            )
        );
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }


    public function __call($method, $arguments)
    {
        if (!isset($this->callCache[$method])) {
            $matches = array();
            if (preg_match('/^get(.+)APICollection$/', $method, $matches)) {
                $this->callCache[$method] = Manager::getService('API\\Collection\\' . $matches[1]);
            } elseif (preg_match('/^get(.+)APIService$/', $method, $matches)) {
                $this->callCache[$method] = Manager::getService('API\\Services\\' . $matches[1]);
            } else {
                throw new APIControllerException('method "' . $method . " not found.", 500);
            }
        }
        return $this->callCache[$method];
    }
}