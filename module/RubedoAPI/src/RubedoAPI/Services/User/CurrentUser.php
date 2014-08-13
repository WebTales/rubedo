<?php
/**
 * Created by PhpStorm.
 * User: gael
 * Date: 13/08/14
 * Time: 11:39
 */

namespace RubedoAPI\Services\User;


use Rubedo\Services\Manager;
use RubedoAPI\Traits\LazyServiceManager;

class CurrentUser extends \Rubedo\User\CurrentUser {
    use LazyServiceManager;
    /** @var  array */
    static public $token;

    /** @var  \Rubedo\Interfaces\Collection\IUsers */
    protected $usersCollection;

    public function isAuthenticated ()
    {
        $accessToken = $this->getAccessToken();
        if (!empty($accessToken))
            return true;
        return parent::isAuthenticated();
    }

    protected function _fetchCurrentUser ()
    {
        $serviceReader = Manager::getService('Users');
        $user = $serviceReader->findById($this->getAccessToken()['user']['id']);
        if (!empty($user))
            return $user;
        return parent::_fetchCurrentUser();
    }
    protected function getAccessToken() {
        if (!isset(static::$token)) {
            $queryArray = Manager::getService('Application')->getRequest()->getQuery()->toArray();
            if (!isset($queryArray['access_token'])) return null;
            $accessToken = $this->getUserTokensAPICollection()->findOneByAccessToken($queryArray['access_token']);
            if (empty($accessToken)) return null;
            return static::$token = $accessToken;
        }
        return static::$token;
    }

    public function getCurrentUser ()
    {
        if (! isset(static::$_currentUser)) {
            if ($this->isAuthenticated()) {
                $user = $this->_fetchCurrentUser();

                static::$_currentUser = $user;
                if ($user) {
                    $mainWorkspace = $this->getMainWorkspace();
                    if ($mainWorkspace) {
                        $user['defaultWorkspace'] = $mainWorkspace['id'];
                        static::$_currentUser = $user;
                    }
                }
            }
        }
        return static::$_currentUser;
    }
}