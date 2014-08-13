<?php

namespace RubedoAPI\Entities\API;

use RubedoAPI\Traits\LazyServiceManager;

class Identity {
    use LazyServiceManager;

    function __construct($access_token) {

    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        $currentUserService = $this->getCurrentUserAPIService();
        return $currentUserService::$token;
    }

    /**
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @return array
     */
    public function getUser()
    {
        return $this->getCurrentUserAPIService()->getCurrentUser();
    }
}