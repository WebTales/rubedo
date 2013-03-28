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

use Rubedo\Interfaces\Collection\IFormsResponses;

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
    public function getValidResponsesByFormId ($formId,$start = null, $limit = null)
    {
        $filter = array();
        $filter[] = array(
            'property' => 'status',
            'value' => 'finished'
        );
        $filter[] = array(
            'property' => 'formId',
            'value' => $formId
        );
        
        $sort = array(
            'property' => 'lastUpdateTime',
            'direction' => 'ASC'
        );
        
        return $this->getList($filter, $sort,$start,$limit);
    }
}
