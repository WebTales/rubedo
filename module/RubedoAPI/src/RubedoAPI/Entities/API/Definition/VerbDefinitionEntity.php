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

namespace RubedoAPI\Entities\API\Definition;


use RubedoAPI\Exceptions\APIAuthException;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Exceptions\APIFilterException;
use RubedoAPI\Exceptions\APIRequestException;
use RubedoAPI\Traits\LazyServiceManager;
use Zend\Stdlib\JsonSerializable;

/**
 * Class VerbDefinitionEntity
 * @package RubedoAPI\Entities\API\Definition
 */
class VerbDefinitionEntity implements JsonSerializable
{
    use LazyServiceManager;
    /**
     * @var string
     */
    protected $verb;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var array
     */
    protected $outputFilters = [];
    /**
     * @var array
     */
    protected $inputFilters = [];
    /**
     * @var array
     */
    protected $rights = array();

    /**
     * Construct default verb definition
     * @param $verb
     */
    function __construct($verb)
    {
        $this
            ->setVerb($verb)
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Access token')
                    ->setKey('access_token')
                    ->setRename('identity')
                    ->setFilter('\\RubedoAPI\\Entities\\API\\Identity')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Locale')
                    ->setKey('lang')
                    ->setFilter('\\RubedoAPI\\Entities\\API\\Language')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('success')
                    ->setRequired()
                    ->setDescription('Success of the query')
                    ->setFilter('boolean')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('message')
                    ->setDescription('Informations about the query')
                    ->setFilter('string')
            );
    }

    /**
     * Get description
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param mixed $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get verb
     *
     * @return mixed
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * Set verb
     *
     * @param mixed $verb
     * @return $this
     */
    public function setVerb($verb)
    {
        $this->verb = $verb;
        return $this;
    }

    /**
     * Helper : Check if identity is required
     *
     * @return bool
     */
    public function hasIdentityRequired()
    {
        return $this->getInputFilter('access_token')->isRequired();
    }

    /**
     * Helper : Require identity
     *
     * @param bool $has
     * @return $this
     */
    public function identityRequired($has = true)
    {
        $this->editInputFilter('access_token', function (FilterDefinitionEntity &$filter) use ($has) {
            $filter->setRequired($has);
        });
        return $this;
    }

    /**
     * Add a right
     *
     * @param $right
     * @return $this
     * @internal param array $rights
     */
    public function addRight($right)
    {
        $this->rights[] = $right;
        if (!$this->hasIdentityRequired())
            $this->identityRequired();

        return $this;
    }

    /**
     * Get all rights
     *
     * @return array
     */
    public function getRights()
    {
        return $this->rights;
    }

    public function checkRights()
    {
        foreach ($this->getRights() as $right) {
            if (!$this->getAclService()->hasAccess($right)) {
                throw new APIAuthException('User access denied ("' . $right . '")', 403, true);
            }
        }
    }

    /**
     * Get input filters list
     *
     * @return array
     */
    public function getInputFilters()
    {
        return $this->inputFilters;
    }

    /**
     * Get input filter
     *
     * @param $key
     * @return FilterDefinitionEntity
     */
    public function getInputFilter($key)
    {
        if (!isset($this->inputFilters[$key]))
            return new FilterDefinitionEntity();
        return $this->inputFilters[$key];
    }

    /**
     * Get output filter
     *
     * @param $key
     * @return FilterDefinitionEntity
     */
    public function getOutputFilter($key)
    {
        if (!isset($this->outputFilters[$key]))
            return new FilterDefinitionEntity();
        return $this->outputFilters[$key];
    }

    /**
     * Add input filter
     *
     * @param \RubedoAPI\Entities\API\Definition\FilterDefinitionEntity $inputFilter
     * @return $this
     */
    public function addInputFilter(FilterDefinitionEntity $inputFilter)
    {
        $this->inputFilters[$inputFilter->getKey()] = $inputFilter;
        return $this;
    }

    /**
     * Edit input filter
     *
     * @param $key
     * @param $function
     * @return $this
     */
    public function editInputFilter($key, $function)
    {
        if (!isset($this->inputFilters[$key])) {
            $filter = (new FilterDefinitionEntity())->setKey($key);
            $function($filter);
            $this->addInputFilter($filter);
        } else
            $function($this->inputFilters[$key]);
        return $this;
    }

    /**
     * Get output filters list
     *
     * @return array
     */
    public function getOutputFilters()
    {
        return $this->outputFilters;
    }

    /**
     * Filter input
     *
     * @param $toFilter
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @throws \RubedoAPI\Exceptions\APIFilterException
     */
    public function filterInput($toFilter)
    {
        $filtered = [];
        foreach ($this->getInputFilters() as $key => $filter) {
            if (!($filter instanceof FilterDefinitionEntity))
                throw new APIEntityException('Filter in VerbDefinition must be FilterDefinitionEntity', 500);
            if ($filter->isRequired() && !array_key_exists($key, $toFilter))
                throw new APIFilterException('"' . $key . '" is required', 500);
            elseif (!array_key_exists($key, $toFilter))
                continue;
            $filtered[$filter->hasRename() ? $filter->getRename() : $key] = $filter->filter($toFilter[$key]);

        }
        return $filtered;
    }

    /**
     * Filter output
     *
     * @param $toFilter
     * @return array
     * @throws \RubedoAPI\Exceptions\APIRequestException
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @throws \RubedoAPI\Exceptions\APIFilterException
     */
    public function filterOutput($toFilter)
    {
        if (!isset($toFilter) || !is_array($toFilter))
            throw new APIRequestException('Each action must return an array.', 500);
        $filtered = [];
        foreach ($this->getOutputFilters() as $key => $filter) {
            if (!($filter instanceof FilterDefinitionEntity))
                throw new APIEntityException('Filter in VerbDefinition must be FilterDefinitionEntity', 500);
            if ($filter->isRequired() && !array_key_exists($key, $toFilter))
                throw new APIFilterException('The parameter "' . $key . '" must be present in API response.', 500);
            elseif (!array_key_exists($key, $toFilter))
                continue;
            $filtered[$key] = $filter->filter($toFilter[$key]);

        }
        return $filtered;
    }

    /**
     * Add output filter
     *
     * @param \RubedoAPI\Entities\API\Definition\FilterDefinitionEntity $outputFilter
     * @return $this
     */
    public function addOutputFilter(FilterDefinitionEntity $outputFilter)
    {
        $this->outputFilters[$outputFilter->getKey()] = $outputFilter;
        return $this;
    }

    /**
     * Edit output filter
     *
     * @param $key
     * @param $function
     * @return $this
     */
    public function editOutputFilter($key, $function)
    {
        if (!isset($this->outputFilters[$key])) {
            $filter = (new FilterDefinitionEntity())->setKey($key);
            $function($filter);
            $this->addOutputFilter($filter);
        } else
            $function($this->outputFilters[$key]);
        return $this;
    }

    /**
     * Get filters serialized
     *
     * @param array $filterArray
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
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

    /**
     * Helper : Get output filters serialized
     *
     * @return array
     */
    protected function getOutputFiltersSerialized()
    {
        return $this->getFiltersSerialized($this->getOutputFilters());
    }

    /**
     * Helper : Get input filters serialized
     *
     * @return array
     */
    protected function getInputFiltersSerialized()
    {
        return $this->getFiltersSerialized($this->getInputFilters());
    }

    /**
     * Return jsonserializable array
     *
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'verb' => $this->getVerb(),
            'description' => $this->getDescription(),
            'input' => $this->getInputFiltersSerialized(),
            'output' => $this->getOutputFiltersSerialized(),
        ];
    }
}
