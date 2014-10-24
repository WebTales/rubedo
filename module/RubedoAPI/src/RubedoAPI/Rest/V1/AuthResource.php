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

namespace RubedoAPI\Rest\V1;

use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;

/**
 * Class AuthResource
 * @package RubedoAPI\Rest\V1
 */
class AuthResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Authentication')
            ->setDescription('Login in the Rubedo API')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->identityRequired()
                    ->setDescription('Login in the Rubedo API')
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setDescription('My rights')
                            ->setKey('rights')
                    );

            });
    }

    /**
     * Custom options to provide authentication means
     * { @inheritdoc }
     *
     * @return array
     */
    public function optionsAction()
    {
        return array_merge(parent::optionsAction(), $this->getAuthMeans());
    }

    /**
     * Return Rights for front
     *
     * @param $params
     * @return array
     */
    public function getAction($params)
    {

        return array(
            'success' => true,
            'rights' => array(
                'boAccess' => $this->getAclService()->hasAccess('ui.backoffice'),
                'canEdit' => $this->getAclService()->hasAccess('write.frontoffice.contents'),
            ),
        );
    }

    /**
     * return authentication means and API uri
     *
     * @return array
     */
    protected function getAuthMeans()
    {
        return [
            'means' => [
                'oauth2' => '/api/v1/auth/oauth2',
            ],
        ];
    }
}