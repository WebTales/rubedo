<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 06/08/14
 * Time: 13:47
 */

namespace RubedoAPI\Rest\V1;


use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Interfaces\IRessource;
use RubedoAPI\Tools\DefinitionEntity;

abstract class AbstractRessource implements IRessource {
    protected $config = [];
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


}