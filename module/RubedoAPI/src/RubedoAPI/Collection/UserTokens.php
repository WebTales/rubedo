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

namespace RubedoAPI\Collection;

use Rubedo\Collection\AbstractCollection;
use RubedoAPI\Exceptions\APIEntityException;
use WebTales\MongoFilters\Filter;

/**
 * Class UserTokens
 * @package RubedoAPI\Collection
 */
class UserTokens extends AbstractCollection
{
    /**
     * Complete collection properties
     */
    public function __construct()
    {
        $this->_collectionName = 'UserTokens';
        parent::__construct();
    }

    /**
     * Find user token from refresh token
     *
     * @param string $refreshToken the refresh token
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function findOneByRefreshToken($refreshToken)
    {
        $filter = Filter::factory('Value');
        $filter->setName('refresh_token')->setValue($refreshToken);
        $token = $this->_dataService->findOne($filter);
        if (empty($token))
            throw new APIEntityException('Refresh token not found', 404, true);
        return $token;
    }

    /**
     * Find user token from access token
     *
     * @param string $accessToken the access token
     * @return array
     * @throws \RubedoAPI\Exceptions\APIEntityException
     */
    public function findOneByAccessToken($accessToken, $updateToken = null)
    {
        $filter = Filter::factory('Value');
        $filter->setName('access_token')->setValue($accessToken);
        $token = !empty($updateToken)? $updateToken : $this->_dataService->findOne($filter);
        if (empty($token))
            throw new APIEntityException('Access token not found', 404, true);
        if (($token['lifetime'] + $token['createTime']) < time())
            throw new APIEntityException('Access token is expired', 403, true);

        return $token;
    }
}
