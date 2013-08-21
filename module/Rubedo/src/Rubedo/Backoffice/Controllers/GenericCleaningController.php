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
namespace Rubedo\Backoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
/**
 * Controller providing CRUD API for the icons JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class GenericCleaningController extends DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'read-child',
        'tree',
        'clear-orphans',
        'count-orphans'
    );

    public function __construct ()
    {
        parent::__construct();
    }

    public function clearOrphansAction ()
    {
        $iconsService = Rubedo\Services\Manager::getService('Icons');
        $personalPrefsService = Rubedo\Services\Manager::getService('PersonalPrefs');
        $taxonomyTermsService = Rubedo\Services\Manager::getService('TaxonomyTerms');
        $pagesService = Rubedo\Services\Manager::getService('Pages');
        $contentsService = Rubedo\Services\Manager::getService('Contents');
        $groupsService = Rubedo\Services\Manager::getService('Groups');
        
        $results = array();
        
        $results['icons'] = $iconsService->clearOrphanIcons();
        $results['personal prefs'] = $personalPrefsService->clearOrphanPrefs();
        $results['taxonomy terms'] = $taxonomyTermsService->clearOrphanTerms();
        $results['pages'] = $pagesService->clearOrphanPages();
        $results['contents'] = $contentsService->clearOrphanContents();
        $results['groups'] = $groupsService->clearOrphanGroups();
        
        return $this->_returnJson($results);
    }

    public function countOrphansAction ()
    {
        $iconsService = Rubedo\Services\Manager::getService('Icons');
        $personalPrefsService = Rubedo\Services\Manager::getService('PersonalPrefs');
        $taxonomyTermsService = Rubedo\Services\Manager::getService('TaxonomyTerms');
        $pagesService = Rubedo\Services\Manager::getService('Pages');
        $contentsService = Rubedo\Services\Manager::getService('Contents');
        $groupsService = Rubedo\Services\Manager::getService('Groups');
        
        $results = array();
        
        $results['icons'] = $iconsService->countOrphanIcons();
        $results['personal prefs'] = $personalPrefsService->countOrphanPrefs();
        $results['taxonomy terms'] = $taxonomyTermsService->countOrphanTerms();
        $results['pages'] = $pagesService->countOrphanPages();
        $results['contents'] = $contentsService->countOrphanContents();
        $results['groups'] = $groupsService->countOrphanGroups();
        
        return $this->_returnJson($results);
    }
}