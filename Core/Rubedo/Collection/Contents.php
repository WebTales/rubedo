<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IContents;

/**
 * Service to handle contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Contents extends WorkflowAbstractCollection implements IContents
{

    public function __construct() {
        $this->_collectionName = 'Contents';
        parent::__construct();
    }

    /**
     * ensure that no nested contents are requested directly
     */
    protected function _init() {
        parent::_init();
        $this->_dataService->addToExcludeFieldList(array('nestedContents'));
    }

}
