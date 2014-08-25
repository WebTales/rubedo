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

namespace RubedoAPI\Services\Security;

use Rubedo\Services\Events;
use Rubedo\Services\Manager;
use Rubedo\User\Authentication\Adapter\CoreAdapter;
use Rubedo\User\Authentication\AuthenticationService;
use RubedoAPI\Exceptions\APIAuthException;
use RubedoAPI\Exceptions\APIEntityException;
use RubedoAPI\Traits\LazyServiceManager;

/**
 * Class Authentication
 * @package RubedoAPI\Services\Security
 */
class Authentication extends AuthenticationService
{
    use LazyServiceManager;

    /**
     * Authentication with login/password to generate token
     *
     * @param $login
     * @param $password
     * @return array
     * @throws \RubedoAPI\Exceptions\APIAuthException
     */
    public function APIAuth($login, $password)
    {
        $authAdapter = new CoreAdapter($login, $password);
        $result = parent::authenticate($authAdapter);
        if (!$result->isValid()) {
            Events::getEventManager()->trigger(self::FAIL, null, array(
                'login' => $login,
                'error' => $result->getMessages()
            ));
            throw new APIAuthException('Bad credentials', 401);
        }
        Events::getEventManager()->trigger(self::SUCCESS);
        $this->getCurrentUserAPIService()->getToken();
        $identity = $result->getIdentity();
        $myToken = $this->getTokenAPIService()->generateBearerToken($identity['id']);
        return array(
            'token' => $myToken,
            'user' => $identity,
        );
    }

    /**
     * Generate new token from refresh token
     *
     * @param $refreshToken
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function APIRefreshAuth($refreshToken)
    {
        $oldToken = $this->getUserTokensAPICollection()->findOneByRefreshToken($refreshToken);
        $user = $this->getUsersCollection()->findById($oldToken['user']['id']);
        if (empty($user))
            throw new APIEntityException('User not found', 404);
        $myToken = $this->getTokenAPIService()->generateBearerToken($oldToken['user']['id']);
        $this->getUserTokensAPICollection()->destroy($oldToken);
        return array(
            'token' => $myToken,
            'user' => $user,
        );
    }

}