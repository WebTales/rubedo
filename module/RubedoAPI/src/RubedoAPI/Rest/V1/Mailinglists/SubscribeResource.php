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

namespace RubedoAPI\Rest\V1\Mailinglists;


use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use RubedoAPI\Rest\V1\AbstractResource;
use WebTales\MongoFilters\Filter;

/**
 * Class SubscribeResource
 *
 * @package RubedoAPI\Rest\V1
 */
class SubscribeResource extends AbstractResource
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
     * Subscribe to a list of mailing lists
     *
     * @param $params
     * @return array
     */
    public function postAction($params)
    {
        if (is_array($params['mailingLists'])) {
            $mailingLists = &$params['mailingLists'];
        } elseif ($params['mailingLists'] === 'all') {
            $mailingLists = array();
            foreach ($this->getMailingListCollection()->getList()['data'] as $mailingListAvailable) {
                $mailingLists[] = $mailingListAvailable['id'];
            }
        } else {
            $mailingLists = (array)$params['mailingLists'];
        }
        if (empty($params['name'])) {
            $params['name'] = null;
        }
        if (empty($params['fields'])) {
            $params['fields'] = array();
        } else {
            $filters = Filter::factory();
            $filters->addFilter(Filter::factory('Value')->setName('UTType')
                ->setValue("email"));
            $fieldsFromType = $this->getUserTypesCollection()->findOne($filters)['fields'];
            $existingFields = array();
            foreach ($fieldsFromType as $userTypeField) {
                $existingFields[] = $userTypeField['config']['name'];
            }
            foreach ($params['fields'] as $fieldName => $fieldValue) {
                unset($fieldValue); //unused
                if (!in_array($fieldName, $existingFields)) {
                    unset ($params['fields'][$fieldName]);
                }
            }
        }
        foreach ($mailingLists as &$mailingListTargeted) {
            $result = $this->getMailingListCollection()->subscribe($mailingListTargeted, $params['email'], false, $params['name'], $params['fields']);
            if (!$result['success']) {
                return $result;
            }
        }
        return array(
            'success' => true
        );
    }

    /**
     * Unsubscribe to a list of mailing lists
     *
     * @param $params
     * @return array
     */
    public function deleteAction($params)
    {
        if ($params['mailingLists'] === 'all') {
            return $this->getMailingListCollection()->unSubscribeFromAll($params['email']);
        } else {
            $mailingLists = (array)$params['mailingLists'];
            foreach ($mailingLists as &$mailingListTargeted) {
                $result = $this->getMailingListCollection()->unSubscribe($mailingListTargeted, $params['email']);
                if (!$result) {
                    return array(
                        'success' => false
                    );
                }
            }
        }
        return array(
            'success' => true
        );
    }

    /**
     * Define the resource
     */
    protected function define()
    {
        $this
            ->definition
            ->setName('Subscribe To MailingList')
            ->setDescription('Subscribe to a mailing list')
            ->editVerb('post', function (VerbDefinitionEntity &$definition) {
                $this->definePost($definition);
            })
            ->editVerb('delete', function (VerbDefinitionEntity &$definition) {
                $this->defineDelete($definition);
            });
    }

    /**
     * define post
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function definePost(VerbDefinitionEntity &$definition)
    {
        $definition
            ->setDescription('Subscribe to a list of mailing lists')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('email')
                    ->setDescription('Email targeted by the query')
                    ->setFilter('validate_email')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('mailingLists')
                    ->setDescription('Array or string of id to delete. "all" target all mailing lists.')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('name')
                    ->setDescription('Name for user')
                    ->setFilter('string')
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setDescription('Fields associated to user')
                    ->setKey('fields')
            );
    }

    /**
     * define delete
     *
     * @param VerbDefinitionEntity $definition
     */
    protected function defineDelete(VerbDefinitionEntity &$definition)
    {
        $definition
            ->setDescription('Unsubscribe to a list of mailing lists')
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('email')
                    ->setDescription('Email targeted by the query')
                    ->setFilter('validate_email')
                    ->setRequired()
            )
            ->addInputFilter(
                (new FilterDefinitionEntity())
                    ->setKey('mailingLists')
                    ->setDescription('Array or string of id to delete. "all" target all mailing lists.')
                    ->setRequired()
            );
    }
}