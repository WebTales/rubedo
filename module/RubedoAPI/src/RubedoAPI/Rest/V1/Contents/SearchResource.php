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

namespace RubedoAPI\Rest\V1\Contents;

use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\SearchResource as GlobalSearch;

/**
 * Class SearchResource
 * @package RubedoAPI\Rest\V1\Contents
 */
class SearchResource extends GlobalSearch
{
    public function __construct()
    {
        parent::__construct();
        $this->searchOption = 'content';
        $this
            ->definition
            ->setName('Contents')
            ->setDescription('Deal with contents')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setName('Get a list of contents')
                    ->setDescription('Get a list of contents using Elastic Search');
            });
    }
}