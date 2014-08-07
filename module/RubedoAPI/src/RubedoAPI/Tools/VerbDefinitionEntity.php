<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 06/08/14
 * Time: 17:16
 */

namespace RubedoAPI\Tools;


use RubedoAPI\Exceptions\APIEntityException;
use Zend\Stdlib\JsonSerializable;

class VerbDefinitionEntity implements JsonSerializable {
    protected $verb;
    protected $description;
    protected $outputFilters = [];
    protected $inputFilters = [];


    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * @param mixed $verb
     * @return $this
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
        return $this;
    }

    /**
     * @return array
     */
    public function getInputFilters()
    {
        return $this->inputFilters;
    }

    /**
     * @param \RubedoAPI\Tools\FilterDefinitionEntity $inputFilter
     * @return $this
     */
    public function addInputFilter(FilterDefinitionEntity $inputFilter)
    {
        $this->inputFilters[$inputFilter->getKey()] = $inputFilter;
        return $this;
    }

    /**
     * @return array
     */
    public function getOutputFilters()
    {
        return $this->outputFilters;
    }

    /**
     * @param \RubedoAPI\Tools\FilterDefinitionEntity $outputFilter
     * @return $this
     */
    public function addOutputFilter(FilterDefinitionEntity $outputFilter)
    {
        $this->outputFilters[$outputFilter->getKey()] = $outputFilter;
        return $this;
    }

    protected function getFiltersSerialized(array $filterArray)
    {
        $filters = [];
        foreach ($filterArray as $filterName => $filter) {
            if (!$filter instanceof FilterDefinitionEntity)
                throw new APIEntityException('Filter in VerbDefinition must be FilterDefinitionEntity', 500);
            $filters[$filterName] = $filter->jsonSerialize();
        }
        return $filters;
    }

    protected function getOutputFiltersSerialized()
    {
        return $this->getFiltersSerialized($this->getOutputFilters());
    }

    protected function getInputFiltersSerialized()
    {
        return $this->getFiltersSerialized($this->getInputFilters());
    }

    function jsonSerialize() {
        return [
            'verb' => $this->getVerb(),
            'description' => $this->getDescription(),
            'input' => $this->getInputFiltersSerialized(),
            'output' => $this->getOutputFiltersSerialized(),
        ];
    }
}