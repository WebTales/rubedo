<?php
/**
 * Created by PhpStorm.
 * User: gael
 * Date: 13/08/14
 * Time: 17:10
 */

namespace RubedoAPI\Entities\API;


use RubedoAPI\Exceptions\APIEntityException;

class Language {
    protected $locale;
    protected $fallback;

    function __construct($languageString)
    {
        $lengthString = strlen($languageString);
        if (2 == $lengthString) {
            $this->locale = $languageString;
            return;
        }
        if (5 == $lengthString) {
            $this->locale = substr($languageString, 0, 2);
            $this->fallback = substr($languageString, -2, 2);
            return;
        }
        throw new APIEntityException('Language value is not compliant', 400);
    }

    /**
     * @return mixed
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    public function hasFallback()
    {
        return isset($this->fallback);
    }
} 