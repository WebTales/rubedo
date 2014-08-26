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

use RubedoAPI\Rest\V1\AbstractRessource;

/**
 * Class MediaRessource
 * @package RubedoAPI\Rest\V1
 */
class MediaRessource extends AbstractRessource
{
    public function __construct()
    {
        parent::__construct();
    }

    public function optionsAction()
    {
        return array_merge(parent::optionsAction(), $this->getMediaMeans());
    }

    protected function getMediaMeans()
    {
        return [
            'means' => [
                'search' => '/api/v1/media/search'
            ]
        ];
    }
}