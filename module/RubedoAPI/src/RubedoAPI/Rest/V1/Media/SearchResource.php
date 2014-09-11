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

namespace RubedoAPI\Rest\V1\Media;

use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\SearchResource as GlobalSearch;

/**
 * Class SearchResource
 * @package RubedoAPI\Rest\V1\Media
 */
class SearchResource extends GlobalSearch
{
    public function __construct()
    {
        parent::__construct();
        $this->searchOption = 'dam';
        $this
            ->definition
            ->setName('Media')
            ->setDescription('Deal with media')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a list of media using Elastic Search');
            });
    }

    protected function injectDataInResults(&$results, $params)
    {
        foreach ($results['data'] as $key => $value) {
            $results['data'][$key]['fileSize'] = $this->humanfilesize($value['fileSize']);
            $urlService = $this->getUrlAPIService();
            $results['data'][$key]['url'] = $urlService->mediaUrl($results['data'][$key]['id']);
        }
    }

    protected function humanfilesize ($bytes, $decimals = 0)
    {
        $size = array(
            'B',
            'kB',
            'MB',
            'GB',
            'TB',
            'PB',
            'EB',
            'ZB',
            'YB'
        );
        $factor = floor((strlen($bytes[0]) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes[0] / pow(1024, $factor)) . @$size[$factor];
    }
}