<?php

namespace RubedoAPI\Rest\V1;

use Rubedo\Services\Manager;
use RubedoAPI\Tools\FilterDefinitionEntity;
use RubedoAPI\Tools\VerbDefinitionEntity;

class MenuRessource extends AbstractRessource {
    private $pageService;
    private $rootline;
    private $excludeFromMenuCondition;

    public function __construct()
    {
        parent::__construct();
        $this
            ->definition
            ->setName('Menu')
            ->setDescription('Deal with menu')
            ->editVerb('get', function(VerbDefinitionEntity &$entity) {
                $entity
                    ->setDescription('Get a menu tree from id')
                    ->addInputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('pageId')
                            ->setRequired()
                            ->setDescription('Id of the root page for the menu')
                            ->setFilter('\\MongoId')
                    )
                    ->addOutputFilter(
                        (new FilterDefinitionEntity())
                            ->setKey('menu')
                            ->setDescription('The recursive menu')
                    )
                ;
            })
        ;
        $this->pageService = Manager::getService('Pages');
        $this->urlService = Manager::getService('Url');
    }

    public function getAction($params) {
        $rootPage = $this->pageService->findById($params['pageId']);
        $startLevel = 1;
        $levelOnePages = $this->_getPagesByLevel($rootPage['id'], $startLevel);
        $menu = array_intersect_key($rootPage, array_flip(array('text', 'id')));
        $urlOptions = array(
            'encode' => true,
            'reset' => true
        );
        foreach ($levelOnePages as $page) {
            $tempArray = array();
            $tempArray['url'] = $this->getContext()->url()->fromRoute('rewrite', array(
                'pageId' => $page['id'],
                "locale" => 'fr'
            ), $urlOptions);

            $tempArray['title'] = $page['title'];
            $tempArray['text'] = $page['text'];
            $tempArray['id'] = $page['id'];
            $levelTwoPages = $this->pageService->readChild($page['id'], $this->excludeFromMenuCondition);

            if (count($levelTwoPages)) {

                $tempArray['pages'] = array();
                foreach ($levelTwoPages as $subPage) {
                    $tempSubArray = array();
                    $tempSubArray['url'] = $this->getContext()->url()->fromRoute('rewrite', array(
                        'pageId' => $subPage['id'],
                        "locale" => 'fr'
                    ), $urlOptions);

                    $tempSubArray['title'] = $subPage['title'];
                    $tempSubArray['text'] = $subPage['text'];
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