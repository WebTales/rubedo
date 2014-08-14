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

/**
 * Class Authentication
 * @package RubedoAPI\Services\Security
 */
class Authentication extends AuthenticationService
{
    /** @var  \RubedoAPI\Services\Security\Token */
    protected $tokenService;
    /** @var  \RubedoAPI\Collection\UserTokens */
    protected $userTokenCollection;
    /** @var \Rubedo\Interfaces\Collection\IUsers */
    protected $usersCollection;

    /**
     * Legacy
     *
     * @param \Zend\Authentication\Storage\StorageInterface $storage
     * @param \Zend\Authentication\Adapter\AdapterInterface $adapter
     */
    function __construct(\Zend\Authentication\Storage\StorageInterface $storage = null, \Zend\Authentication\Adapter\AdapterInterface $adapter = null)
    {
        parent::__construct($storage, $adapter);
        $this->tokenService = Manager::getService('API\\Services\\Token');
        $this->userTokenCollection = Manager::getService('API\\Collection\\UserTokens');
        $this->usersCollection = Manager::getService('Users');

    }

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
        Manager::getService('CurrentUser')->getToken();
        $identity = $result->getIdentity();
        $myToken = $this->tokenService->generateBearerToken($identity['id']);
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
        $oldToken = $this->userTokenCollection->findOneByRefreshToken($refreshToken);
        $user = $this->usersCollection->findById($oldToken['user']['id']);
        if (empty($user))
            throw new APIEntityException('User not found', 404);
        $myToken = $this->tokenService->generateBearerToken($oldToken['user']['id']);
        $this->userTokenCollection->destroy($oldToken);
        return array(
            'token' => $myToken,
            'user' => $user,
        );
    }

}