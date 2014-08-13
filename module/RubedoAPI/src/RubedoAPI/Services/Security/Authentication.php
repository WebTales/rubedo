<?php

namespace RubedoAPI\Services\Security;

use Rubedo\Services\Events;
use Rubedo\Services\Manager;
use Rubedo\User\Authentication\Adapter\CoreAdapter;
use Rubedo\User\Authentication\AuthenticationService;
use RubedoAPI\Exceptions\APIAuthException;
use RubedoAPI\Exceptions\APIEntityException;

class Authentication extends AuthenticationService {
    /** @var  \RubedoAPI\Services\Security\Token */
    protected $tokenService;
    /** @var  \RubedoAPI\Collection\UserTokens */
    protected $userTokenCollection;
    /** @var \Rubedo\Interfaces\Collection\IUsers */
    protected $usersCollection;

    function __construct(\Zend\Authentication\Storage\StorageInterface $storage = null, \Zend\Authentication\Adapter\AdapterInterface $adapter = null) {
        parent::__construct($storage, $adapter);
        $this->tokenService = Manager::getService('API\\Services\\Token');
        $this->userTokenCollection = Manager::getService('API\\Collection\\UserTokens');
        $this->usersCollection = Manager::getService('Users');

    }

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