<?php
namespace RubedoAPI\Storage;

use Rubedo\Services\Manager;
use ZF\OAuth2\Adapter\MongoAdapter;

class MongoStorage extends MongoAdapter {
    public function __construct() {
        $this->config = array(
            'client_table' => 'Users',
            'access_token_table' => 'oauth_access_tokens',
            'refresh_token_table' => 'oauth_refresh_tokens',
            'code_table' => 'oauth_authorization_codes',
            'user_table' => 'oauth_users',
            'jwt_table' => 'oauth_jwt',
        );
        /** @var \Rubedo\Interfaces\Mongo\IDataAccess $dataAccess */
        $dataAccess = Manager::getService('MongoDataAccess');
        $dataAccess->init($this->config['client_table']);
        $this->db = $dataAccess->getDbName();

        // Unix timestamps might get larger than 32 bits,
        // so let's add native support for 64 bit ints.
        ini_set('mongo.native_long', 1);

    }

    protected function checkPassword($user, $password)
    {
        var_dump($user);
        var_dump($password);
        exit();
        return $this->verifyHash($password, $user['password']);
    }
}