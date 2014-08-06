<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 06/08/14
 * Time: 13:47
 */

namespace RubedoAPI\Rest\V1;


use Rubedo\Services\Manager;

abstract class AbstractRessource {
    protected $config = array();
    protected  function getConfig()
    {
        Manager::getService('Config');
        return $this->config;
    }
} 