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

namespace RubedoAPI\Rest\V1;

class AuthRessource extends AbstractRessource
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Authentication')
            ->setDescription('Login in the Rubedo API');
    }

    public function optionsAction()
    {
        return array_merge(parent::optionsAction(), $this->getAuthMeans());
    }

    protected function getAuthMeans()
    {
        return [
            'means' => [
                'oauth2' => '/api/v1/auth/oauth2',
            ],
        ];
    }
}