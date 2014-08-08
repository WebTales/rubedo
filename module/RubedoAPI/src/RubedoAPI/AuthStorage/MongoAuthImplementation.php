<?php
/**
 * Created by IntelliJ IDEA.
 * User: nainterceptor
 * Date: 08/08/14
 * Time: 10:23
 */

namespace RubedoAPI\AuthStorage;

use Rubedo\Services\Manager;
use ZF\OAuth2\Adapter\MongoAdapter;


class MongoAuthImplementation extends MongoAdapter
{
    /**
     * @var \Rubedo\Interfaces\Security\IHash
     */
    protected $hashService;
    /**
     * @var \Rubedo\Interfaces\Collection\IUsers
     */
    protected $usersService;

    public function __construct()
    {
        $this->config = array(
            'client_table' => 'Users',
            'access_token_table' => 'OauthAccessTokens',
            'refresh_token_table' => 'OauthRefreshTokens',
            'code_table' => 'OauthAuthorizationCodes',
            'user_table' => 'Users',
            'jwt_table' => 'OauthJwt',
        );
        /** @var \Rubedo\Interfaces\Mongo\IDataAccess $dataAccess */
        $dataAccess = Manager::getService('MongoDataAccess');
        $dataAccess->init($this->config['user_table']);
        $this->db = $dataAccess->getDbName();
// Unix timestamps might get larger than 32 bits,
// so let's add native support for 64 bit ints.
        ini_set('mongo.native_long', 1);
        $this->hashService = Manager::getService('Hash');
        $this->usersService = Manager::getService('Users');
    }

    public function checkClientCredentials($client_id, $client_secret = null)
    {
        if ($user = $this->collection('user_table')->findOne(array('login' => $client_id))) {
            return $this->hashService->checkPassword($user['password'], $client_secret, $user['salt']);
        }
        return false;
    }

    public function getAccessToken($access_token)
    {
        $token = $this->collection('access_token_table')->findOne(array('access_token' => $access_token));
        return is_null($token) ? false : $token;
    }

    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null)
    {
        $client = $this->usersService->findByLogin($client_id);
        if ($this->getAccessToken($access_token)) {
            $this->collection('access_token_table')->update(
                array('access_token' => $access_token),
                array('$set' => array(
                    'client_id' => $client['id'],
                    'client' => array_intersect_key($client, array_flip(array('login', 'id', 'email'))),
                    'expires' => $expires,
                    'user_id' => $user_id,
                    'scope' => $scope,
                )
                )
            );
        } else {
            $this->collection('access_token_table')->insert(
                array(
                    'access_token' => $access_token,
                    'client_id' => $client['id'],
                    'client' => array_intersect_key($client, array_flip(array('login', 'id', 'email'))),
                    'expires' => $expires,
                    'user_id' => $user_id,
                    'scope' => $scope,
                )
            );
        }
        return true;
    }

    /* ClientInterface */
    public function getClientDetails($client_id)
    {
        $result = $this->usersService->findByLogin($client_id);
        return is_null($result) ? false : $result;
    }
}