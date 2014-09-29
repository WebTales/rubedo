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
use WebTales\MongoFilters\Filter;

/**
 * Class MailinglistsResource
 * @package RubedoAPI\Rest\V1
 */
class MailinglistsResource extends AbstractResource
{
    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this->define();
    }

    /**
     * Get a list of mailing lists
     * @return array
     */
    public function getAction()
    {
        $mailinglists = $this->getMailingListCollection()->getList()['data'];
        foreach ($mailinglists as &$mailingList) {
            $mailingList = $this->filterMailingList($mailingList);
        }
        $filters = Filter::factory();
        $filters->addFilter(Filter::factory('Value')->setName('UTType')
            ->setValue("email"));
        $userType = $this->getUserTypesCollection()->findOne($filters);
        return array(
            'success' => true,
            'mailinglists' => $mailinglists,
            'userType' => $userType,
        );
    }

    /**
     * Filter a content type
     *
     * @param array $mailingList
     * @return array
     */
    protected function filterMailingList(array $mailingList)
    {
        return array_intersect_key($mailingList, array_flip(array('id', 'name')));
    }

    /**
     * Define the resource
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('MailingLists')
            ->setDescription('Deal with mailing lists')
            ->editVerb('get', function (VerbDefinitionEntity &$definition) {
                $this->defineGet($definition);
            });
    }

    /**
     * Define get action
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function defineGet(VerbDefinitionEntity &$definition)
    {
        $definition
            ->setDescription('Get a list of mailing lists')
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('mailinglists')
                    ->setDescription('List of mailing lists')
            )
            ->addOutputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('User type')
                    ->setKey('userType')
                    ->setRequired()
            );
    }
}