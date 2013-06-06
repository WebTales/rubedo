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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IFormsResponses, WebTales\MongoFilters\Filter;

/**
 * Service to handle Delegations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class FormsResponses extends AbstractCollection implements IFormsResponses
{

    public function __construct ()
    {
        $this->_collectionName = 'FormsResponses';
        parent::__construct();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\IFormsResponses::getValidResponsesByFormId()
     */
    public function getValidResponsesByFormId ($formId, $start = null, $limit = null)
    {
        $filters = Filter::Factory();
        $filters->addFilter(Filter::Factory('Value')->setName('status')
            ->setValue('finished'));
        $filters->addFilter(Filter::Factory('Value')->setName('formId')
            ->setValue($formId));
        
        $sort = array();
        $sort[] = array(
            'property' => 'lastUpdateTime',
            'direction' => 'ASC'
        );
        
        return $this->getList($filters, $sort, $start, $limit);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\IFormsResponses::getValidResponsesByFormId()
     */
    public function getResponsesByFormId ($formId, $start = null, $limit = null)
    {
        $filters = Filter::Factory();
        $filters->addFilter(Filter::Factory('Value')->setName('formId')
            ->setValue($formId));
        
        $sort = array();
        $sort[] = array(
            'property' => 'lastUpdateTime',
            'direction' => 'ASC'
        );
        
        return $this->getList($filters, $sort, $start, $limit);
    }

    public function countValidResponsesByFormId ($formId)
    {
        $filters = Filter::Factory();
        $filters->addFilter(Filter::Factory('Value')->setName('status')
            ->setValue('finished'));
        $filters->addFilter(Filter::Factory('Value')->setName('formId')
            ->setValue($formId));
        return $this->count($filters);
    }

    public function countInvalidResponsesByFormId ($formId)
    {
        $filters = Filter::Factory();
        $filters->addFilter(Filter::Factory('Not')->setName('status')
            ->setValue('finished'));
        $filters->addFilter(Filter::Factory('Value')->setName('formId')
            ->setValue($formId));
        return $this->count($filters);
    }
}
