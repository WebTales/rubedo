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

    public static function setCurrentUser($user)
    {
        static::$_currentUser = $user;
        static::$_currentUserId = $user['id'];
    }

    public function isAuthenticated ()
    {
        $accessToken = $this->getAccessToken();
        return !empty($accessToken);
    }

    protected function _fetchCurrentUser ()
    {
        $serviceReader = Manager::getService('Users');
        return $serviceReader->findById($this->getAccessToken()['user']['id']);
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