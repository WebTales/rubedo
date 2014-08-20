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
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Entities\API\Definition\FilterDefinitionEntity;
use RubedoAPI\Entities\API\Definition\VerbDefinitionEntity;
use WebTales\MongoFilters\Filter;

/**
 * Class MenuRessource
 * @package RubedoAPI\Rest\V1
 */
class MenuRessource extends AbstractRessource
{
    /**
     * @var static
     */
    private $pageService;

    private $rootline;

    private $excludeFromMenuCondition;

    /**
     * { @inheritdoc }
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Menu')
            ->setDescription('Deal with menu')
            ->editVerb('get', function (VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a menu tree from id')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('pageId')
                            ->setRequired()
                            ->setDescription('Id of the root page for the menu')
                            ->setFilter('\\MongoId')
                    )
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('menuLocale')
                            ->setRequired()
                            ->setDescription('Locale for the menu')
                            ->setFilter('string')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('menu')
                            ->setDescription('The recursive menu')
                    );
            });
        $this->pageService = Manager::getService('Pages');
        $this->urlService = Manager::getService('Url');
    }

    /**
     * Get from /menu
     *
     * @param $params
     * @return array
     */
    public function getAction($params)
    {
        $this->excludeFromMenuCondition = Filter::factory('Not')->setName('excludeFromMenu')->setValue(true);
        $urlOptions = array(
            'encode' => true,
            'reset' => true
        );
        $rootPage = $this->pageService->findById($params['pageId']);
        $startLevel = 1;
        $levelOnePages = $this->_getPagesByLevel($rootPage['id'], $startLevel);
        $menu = array_intersect_key($rootPage, array_flip(array('title', 'id')));
        $menu['url'] = $this->getContext()->url()->fromRoute('rewrite', array(
            'pageId' => $menu['id'],
            'locale' => $params['menuLocale']
        ), $urlOptions);

        foreach ($levelOnePages as &$page) {
            $tempArray = array();
            $tempArray['url'] = $this->getContext()->url()->fromRoute('rewrite', array(
                'pageId' => $page['id'],
                'locale' => $params['menuLocale']
            ), $urlOptions);

            $tempArray['title'] = $page['title'];
            $tempArray['id'] = $page['id'];
            $levelTwoPages = $this->pageService->readChild($page['id'], $this->excludeFromMenuCondition);

            if (count($levelTwoPages)) {

                $tempArray['pages'] = array();
                foreach ($levelTwoPages as $subPage) {
                    $tempSubArray = array();
                    $tempSubArray['url'] = $this->getContext()->url()->fromRoute('rewrite', array(
                        'pageId' => $subPage['id'],
                        "locale" => $params['menuLocale']
                    ), $urlOptions);

                    $tempSubArray['title'] = $subPage['title'];
                    $tempSubArray['id'] = $subPage['id'];
                    $tempArray['pages'][] = $tempSubArray;
                }
            }

            $menu['pages'][] = $tempArray;
        }
        return [
            'success' => true,
            'menu' => $menu,
        ];
    }

    /**
     * Get pages by level
     *
     * @param $rootPage
     * @param $targetLevel
     * @param int $currentLevel
     * @return array
     */
    protected function _getPagesByLevel($rootPage, $targetLevel, $currentLevel = 1)
    {
        $pages = $this->pageService->readChild($rootPage, $this->excludeFromMenuCondition);
        if ($currentLevel == $targetLevel) {
            return $pages;
        }
        foreach ($pages as $page) {
            if (in_array($page['id'], $this->rootline)) {
                return $this->_getPagesByLevel($page['id'], $targetLevel, $currentLevel + 1);
            }
        }
        return array();
    }
}