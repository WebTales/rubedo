<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
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
namespace RubedoAPI\Collection;

use Rubedo\Collection\AbstractCollection;
use RubedoAPI\Exceptions\APIEntityException;
use WebTales\MongoFilters\Filter;

class UserTokens extends AbstractCollection
{
    public function __construct()
    {
        $this->_collectionName = 'UserTokens';
        parent::__construct();
    }

    public function findOneByRefreshToken($refreshToken)
    {
        $filter = Filter::factory('Value');
        $filter->setName('refresh_token')->setValue($refreshToken);
        $token = $this->_dataService->findOne($filter);
        if (empty($token))
            throw new APIEntityException('Refresh token not found', 404, true);
        return $token;
    }

    public function findOneByAccessToken($accessToken)
    {
        $filter = Filter::factory('Value');
        $filter->setName('access_token')->setValue($accessToken);
        $token = $this->_dataService->findOne($filter);
        if (empty($token))
            throw new APIEntityException('Access token not found', 404, true);
        if (($token['lifetime'] + $token['createTime']) < time())
            throw new APIEntityException('Access token is expired', 403, true);

        return $token;
    }
}
