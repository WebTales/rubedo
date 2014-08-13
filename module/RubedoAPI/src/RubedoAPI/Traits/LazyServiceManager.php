<?php

namespace RubedoAPI\Traits;


use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIControllerException;

/**
 * Class LazyServiceManager
 * @package RubedoAPI\Traits
 * @method \RubedoAPI\Collection\UserTokens getUserTokensAPICollection() Return UserTokens collection
 * @method \RubedoAPI\Services\Security\Authentication getAuthAPIService() Return Authentication service
 * @method \RubedoAPI\Services\Router\Url getUrlAPIService() Return Router URL service
 * @method \RubedoAPI\Services\User\CurrentUser getCurrentUserAPIService() Return current User service
 * @method \RubedoAPI\Services\Internationalization\Current getCurrentLocalizationAPIService() Return current localization service
 */
trait LazyServiceManager {
    protected $callCache = array();
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