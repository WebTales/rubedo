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

namespace RubedoAPI\Tools;


use RubedoAPI\Exceptions\APIFilterException;
use Zend\Stdlib\JsonSerializable;

class FilterDefinitionEntity implements JsonSerializable
{
    protected $key;
    protected $rename;
    protected $description;
    protected $required = false;
    protected $multivalued = false;
    protected $filter;
    protected $optionsFilter = [];

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
     * @param mixed $rename
     * @return $this
     */
    public function setRename($rename)
    {
        $this->rename = $rename;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRename()
    {
        return $this->rename;
    }

    /**
     * @return boolean
     */
    public function hasRename()
    {
        return !empty($this->rename);
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
        $this->required = (boolean)$required;
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

    protected function filterElement($key, $value)
    {
        $filterId = filter_id($this->getFilter());
        if ($filterId !== false) {
            $filtered = filter_var($value, $filterId, $this->getOptionsFilter());
            if ($filtered === false) {
                throw new APIFilterException('Filter "' . $key . '" failed', 500);
            }
            return $filtered;
        } else {
            try {
                $objToTest = $this->getFilter();
                $var = new $objToTest($value);
                return $var;
            } catch (\Exception $e) {
                if (!(method_exists($e, 'isTransparent') && $e->isTransparent()))
                    throw new APIFilterException('Can\'t try "' . $this->getFilter() . '" var', 500);
                else
                    throw $e;
            }
        }
    }

    public function filter($key, $toFilter)
    {
        $filter = $this->getFilter();
        if (empty($filter))
            return $toFilter;
        $isArray = is_array($toFilter);
        if ($isArray && !$this->isMultivalued())
            throw new APIFilterException('"' . $key . '" is not multivaluable.', 500);
        elseif ($isArray) {
            $filtered = [];
            foreach ($toFilter as $key => $value) {
                $filtered[filter_var($key, FILTER_SANITIZE_STRING)] = $this->filterElement($key, $value);
            }
        } else {
            $filtered = $this->filterElement($key, $toFilter);
        }
        return $filtered;
    }

    function jsonSerialize()
    {
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
            $array['options'] = & $options;
        return $array;
    }
}