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

namespace RubedoAPI\Services\Security;

use RubedoAPI\Exceptions\APIServiceException;
use RubedoAPI\Traits\LazyServiceManager;

/**
 * Class Token
 * @package RubedoAPI\Services\Security
 */
class Token
{
    use LazyServiceManager;

    /**
     * token lifetime
     */
    const LIFETIME = 3600;

    /**
     * Generate Bearer token
     *
     * @param $userId
     * @return mixed
     * @throws \RubedoAPI\Exceptions\APIServiceException
     */
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
        $creation = $this->getUserTokensAPICollection()->create($token);
        if (!$creation['success'])
            throw new APIServiceException('Can\'t create the bearer token', 500);
        return $creation['data'];
    }

    /**
     * Generate token with random string and userid (to reduce collisions)
     *
     * @param $userId
     * @return String
     */
    protected function newToken($userId)
    {
        return $this->getHashService()->hashString($this->getHashService()->generateRandomString(), $userId);
    }
}