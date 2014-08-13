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
use RubedoAPI\Traits\LazyServiceManager;

/**
 * Class AbstractRessource
 * @package RubedoAPI\Rest\V1
 */
abstract class AbstractRessource implements IRessource {
    use LazyServiceManager;
    protected $context;

    /**
     * @var \RubedoAPI\Tools\DefinitionEntity
     */
    protected $definition;
    function __construct()
    {
        $this->definition = new DefinitionEntity();
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
        $params = $verbDefinition->filterInput($params);
        $this->getCurrentLocalizationAPIService()->injectLocalization($params);

        return $verbDefinition->filterOutput(
            $this->{$method . 'Action'}(
                $params
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
}