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

namespace RubedoAPI\Entities\API;

use RubedoAPI\Traits\LazyServiceManager;

class Identity
{
    use LazyServiceManager;

    function __construct($access_token)
    {

    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        $currentUserService = $this->getCurrentUserAPIService();
        return $currentUserService::$token;
    }

    /**
     * @throws \RubedoAPI\Exceptions\APIEntityException
     * @return array
     */
    public function getUser()
    {
        return $this->getCurrentUserAPIService()->getCurrentUser();
    }
}