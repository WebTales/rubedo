<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 06/08/14
 * Time: 13:47
 */

namespace RubedoAPI\Rest\V1;


use Rubedo\Services\Manager;
use RubedoAPI\Tools\DefinitionEntity;

abstract class AbstractRessource {
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
    public function optionsAction($params) {
        return $this->definition->jsonSerialize();
    }
}