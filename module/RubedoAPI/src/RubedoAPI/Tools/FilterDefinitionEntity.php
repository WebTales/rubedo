<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 07/08/14
 * Time: 10:03
 */

namespace RubedoAPI\Tools;


use Zend\Stdlib\JsonSerializable;

class FilterDefinitionEntity implements JsonSerializable {
    protected $key;
    protected $description;
    protected $required = false;
    protected $multivalued = false;
    protected $filter;
    protected $optionsFilter;

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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param mixed $required
     * @return $this
     */
    public function setRequired($required = true)
    {
        $this->required = (boolean) $required;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isMultivalued()
    {
        return $this->multivalued;
    }

    /**
     * @param mixed $multivalued
     * @return $this
     */
    public function setMultivalued($multivalued = true)
    {
        $this->multivalued = $multivalued;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getOptionsFilter()
    {
        return $this->optionsFilter;
    }

    /**
     * @param mixed $optionsFilter
     * @return $this
     */
    public function setOptionsFilter($optionsFilter)
    {
        $this->optionsFilter = $optionsFilter;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param mixed $filter
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }

    function jsonSerialize() {
        $array = [
            'description' => $this->getDescription(),
        ];
        if ($this->filter != null)
            $array['filter'] = $this->getFilter();
        $options = [];
        if ($this->isRequired())
            $options[] = 'required';
        if ($this->isMultivalued())
            $options[] = 'multivalued';
        if (!empty($options))
            $array['options'] = &$options;
        return $array;
    }
}