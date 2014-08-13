<?php

namespace RubedoAPI\Services\Security;

use Rubedo\Services\Manager;
use RubedoAPI\Exceptions\APIServiceException;

class Token {
    const LIFETIME = 3600;
    /**
     * @var \RubedoAPI\Collection\UserTokens
     */
    protected $userTokensCollection;

    /**
     * @var \Rubedo\Interfaces\Security\IHash
     */
    protected $tokenService;
    function __construct()
    {
        $this->userTokensCollection = Manager::getService('API\\Collection\\UserTokens');
        $this->tokenService = Manager::getService('Hash');
    }

    function generateBearerToken($userId)
    {

        $token = array(
            'access_token' => $this->newToken($userId),
            'refresh_token' => $this->newToken($userId),
            'lifetime' => static::LIFETIME,
            'type' => 'bearer',
            'user' => array(
                'id' => $userId
            ),
        );
        $creation = $this->userTokensCollection->create($token);
        if (!$creation['success'])
            throw new APIServiceException('Can\'t create the bearer token', 500);
        return $creation['data'];
    }

    protected function newToken($userId)
    {
        return $this->tokenService->hashString($this->tokenService->generateRandomString(), $userId);
    }
}