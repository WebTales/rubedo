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
 * Class TranslationsResource
 * @package RubedoAPI\Rest\V1
 */
class TranslationsResource extends AbstractResource {
    /**
     * {@inheritdoc}
     */
    function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Get action
     *
     * @param $params
     * @return array
     */
    public function getAction($params)
    {
        $translations = $this->getTranslationAPIService()->getTranslations($params['lang']->getLocale());
        return array(
            'success' => true,
            'translations' => $translations,
        );
    }

    /**
     * Define verbs
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('Translations')
            ->setDescription('Translations')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $this->defineGet($entity);
            });
    }

    /**
     * Define get
     *
     * @param VerbDefinitionEntity $entity
     */
    protected function defineGet(VerbDefinitionEntity &$entity)
    {
        $entity
            ->setDescription('Get translations')
            ->editInputFilter('lang', function (FilterDefinitionEntity &$filter) {
                $filter
                    ->setRequired();
            })
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Translations')
                    ->setKey('translations')
                    ->setRequired()
            );

    }
}